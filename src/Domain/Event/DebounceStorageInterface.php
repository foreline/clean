<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Shared storage for pending debounced invocations.
 *
 * Implementations persist {@see DebounceState} records in a backend that is
 * shared across independent PHP requests/processes (files, Redis, Memcached,
 * etc.), enabling cross-request debouncing.
 *
 * This interface keeps the domain layer transport-agnostic; concrete backends
 * live in the Infrastructure layer.
 */
interface DebounceStorageInterface
{
    /**
     * Persists (inserts or overwrites) the state for its bucket key.
     *
     * Saving an existing key extends its debounce window — the previous record
     * is replaced by the new one (trailing-edge behaviour).
     *
     * @param DebounceState $state
     */
    public function save(DebounceState $state): void;

    /**
     * Returns the state for the given bucket key, or null if none is pending.
     *
     * @param string $key
     * @return DebounceState|null
     */
    public function get(string $key): ?DebounceState;

    /**
     * Removes the state for the given bucket key.
     *
     * @param string $key
     */
    public function delete(string $key): void;

    /**
     * Returns all pending states whose due time is at or before $nowMs.
     *
     * @param int $nowMs Current epoch time in milliseconds.
     * @return DebounceState[]
     */
    public function due(int $nowMs): array;
}
