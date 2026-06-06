<?php
declare(strict_types=1);

namespace Infrastructure\Event\Debounce;

use Domain\Event\DebounceState;
use Domain\Event\DebounceStorageInterface;
use Memcached;

/**
 * Memcached-backed {@see DebounceStorageInterface}.
 *
 * State bodies are stored as JSON strings keyed by the SHA-1 of the bucket key.
 * Because Memcached cannot enumerate keys, a companion index entry (a JSON map
 * of bucket key => dueAtMs) is maintained to support {@see due()}.
 *
 * Caveat: Memcached is a volatile cache. Under memory pressure it may evict the
 * index or state entries, silently dropping pending debounces. Prefer the file
 * or Redis backends when durability matters; use Memcached only when occasional
 * loss of a coalesced invocation is acceptable.
 *
 * Requires the `memcached` PHP extension.
 */
final class MemcachedDebounceStorage implements DebounceStorageInterface
{
    /** @var Memcached Configured Memcached client. */
    private Memcached $memcached;

    /** @var string Key prefix namespacing all debounce entries. */
    private string $prefix;

    /**
     * @param Memcached $memcached A configured Memcached client.
     * @param string $prefix Key prefix for all debounce entries.
     */
    public function __construct(Memcached $memcached, string $prefix = 'debounce')
    {
        $this->memcached = $memcached;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function save(DebounceState $state): void
    {
        $json = json_encode($state->toArray(), JSON_THROW_ON_ERROR);

        $this->memcached->set($this->stateKey($state->getKey()), $json);

        $index = $this->loadIndex();
        $index[$state->getKey()] = $state->getDueAtMs();
        $this->saveIndex($index);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?DebounceState
    {
        $json = $this->memcached->get($this->stateKey($key));
        if ( !is_string($json) || '' === $json ) {
            return null;
        }

        $data = json_decode($json, true);
        if ( !is_array($data) ) {
            return null;
        }

        return DebounceState::fromArray($data);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        $this->memcached->delete($this->stateKey($key));

        $index = $this->loadIndex();
        unset($index[$key]);
        $this->saveIndex($index);
    }

    /**
     * {@inheritDoc}
     */
    public function due(int $nowMs): array
    {
        $result = [];

        foreach ( $this->loadIndex() as $key => $dueAtMs ) {
            if ( (int)$dueAtMs > $nowMs ) {
                continue;
            }

            $state = $this->get((string)$key);
            if ( null !== $state ) {
                $result[] = $state;
            } else {
                // Orphaned index entry (state evicted/removed): clean it up.
                $index = $this->loadIndex();
                unset($index[$key]);
                $this->saveIndex($index);
            }
        }

        return $result;
    }

    /**
     * Loads the companion due-time index.
     *
     * @return array<string, int>
     */
    private function loadIndex(): array
    {
        $raw = $this->memcached->get($this->indexKey());
        if ( !is_string($raw) || '' === $raw ) {
            return [];
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Persists the companion due-time index.
     *
     * @param array<string, int> $index
     */
    private function saveIndex(array $index): void
    {
        $this->memcached->set($this->indexKey(), json_encode($index, JSON_THROW_ON_ERROR));
    }

    /**
     * @param string $key
     * @return string
     */
    private function stateKey(string $key): string
    {
        return $this->prefix . ':state:' . sha1($key);
    }

    /**
     * @return string
     */
    private function indexKey(): string
    {
        return $this->prefix . ':index';
    }
}
