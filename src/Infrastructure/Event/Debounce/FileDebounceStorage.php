<?php
declare(strict_types=1);

namespace Infrastructure\Event\Debounce;

use Domain\Event\DebounceState;
use Domain\Event\DebounceStorageInterface;
use JsonException;
use RuntimeException;
use Throwable;

/**
 * File-based {@see DebounceStorageInterface}.
 *
 * Each debounce bucket is stored as a single JSON file named after the SHA-1 of
 * its key. Writes are atomic (temp file + rename) and exclusive-locked, making
 * this backend safe for concurrent requests on a shared filesystem.
 *
 * Recommended default backend: no external service required and durable across
 * requests.
 */
final class FileDebounceStorage implements DebounceStorageInterface
{
    /** @var string Absolute path to the storage directory (no trailing separator). */
    private string $directory;

    /**
     * @param string $directory Directory used to store debounce state files.
     * @throws RuntimeException If the directory cannot be created.
     */
    public function __construct(string $directory)
    {
        $directory = rtrim($directory, '/\\');

        if ( !is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory) ) {
            throw new RuntimeException("Unable to create debounce storage directory: {$directory}");
        }

        $this->directory = $directory;
    }

    /**
     * {@inheritDoc}
     *
     * @throws JsonException
     * @throws RuntimeException
     */
    public function save(DebounceState $state): void
    {
        $path = $this->pathFor($state->getKey());
        $json = json_encode($state->toArray(), JSON_THROW_ON_ERROR);

        $tmp = $path . '.' . uniqid('', true) . '.tmp';
        if ( false === file_put_contents($tmp, $json, LOCK_EX) ) {
            throw new RuntimeException("Unable to write debounce state file: {$tmp}");
        }

        if ( !rename($tmp, $path) ) {
            @unlink($tmp);
            throw new RuntimeException("Unable to persist debounce state file: {$path}");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key): ?DebounceState
    {
        return $this->read($this->pathFor($key));
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $key): void
    {
        $path = $this->pathFor($key);
        if ( is_file($path) ) {
            @unlink($path);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function due(int $nowMs): array
    {
        $result = [];

        foreach ( glob($this->directory . DIRECTORY_SEPARATOR . '*.json') ?: [] as $file ) {
            $state = $this->read($file);
            if ( null !== $state && $state->getDueAtMs() <= $nowMs ) {
                $result[] = $state;
            }
        }

        return $result;
    }

    /**
     * Reads and decodes a state file, tolerating missing or corrupt files.
     *
     * @param string $path
     * @return DebounceState|null
     */
    private function read(string $path): ?DebounceState
    {
        if ( !is_file($path) ) {
            return null;
        }

        $json = file_get_contents($path);
        if ( false === $json || '' === $json ) {
            return null;
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if ( !is_array($data) ) {
                return null;
            }
            return DebounceState::fromArray($data);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Maps a bucket key to its storage file path.
     *
     * @param string $key
     * @return string
     */
    private function pathFor(string $key): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . sha1($key) . '.json';
    }
}
