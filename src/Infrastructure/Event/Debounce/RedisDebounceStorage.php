<?php
declare(strict_types=1);

namespace Infrastructure\Event\Debounce;

use Domain\Event\DebounceState;
use Domain\Event\DebounceStorageInterface;
use Redis;

/**
 * Redis-backed {@see DebounceStorageInterface}.
 *
 * State bodies are stored as JSON strings keyed by the SHA-1 of the bucket key.
 * A sorted set ({@see indexKey()}) tracks due times (score = dueAtMs, member =
 * bucket key), enabling efficient, atomic range scans in {@see due()}.
 *
 * Requires the `redis` PHP extension.
 */
final class RedisDebounceStorage implements DebounceStorageInterface
{
    /** @var Redis Connected Redis client. */
    private Redis $redis;

    /** @var string Key prefix namespacing all debounce entries. */
    private string $prefix;

    /**
     * @param Redis $redis A connected Redis client.
     * @param string $prefix Key prefix for all debounce entries.
     */
    public function __construct(Redis $redis, string $prefix = 'debounce')
    {
        $this->redis = $redis;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritDoc}
     */
    public function save(DebounceState $state): void
    {
        $json = json_encode($state->toArray(), JSON_THROW_ON_ERROR);

        $this->redis->set($this->stateKey($state->getKey()), $json);
        $this->redis->zAdd($this->indexKey(), (float)$state->getDueAtMs(), $state->getKey());
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?DebounceState
    {
        $json = $this->redis->get($this->stateKey($key));
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
        $this->redis->del($this->stateKey($key));
        $this->redis->zRem($this->indexKey(), $key);
    }

    /**
     * {@inheritDoc}
     */
    public function due(int $nowMs): array
    {
        $keys = $this->redis->zRangeByScore($this->indexKey(), '-inf', (string)$nowMs);
        if ( !is_array($keys) ) {
            return [];
        }

        $result = [];
        foreach ( $keys as $key ) {
            $state = $this->get((string)$key);
            if ( null !== $state ) {
                $result[] = $state;
            } else {
                // Orphaned index entry (state expired/removed): clean it up.
                $this->redis->zRem($this->indexKey(), (string)$key);
            }
        }

        return $result;
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
