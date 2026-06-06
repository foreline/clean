<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Coordinates debouncing of {@see DebouncedSubscriberInterface} subscribers.
 *
 * The Publisher routes matching subscribers here instead of invoking them
 * directly, allowing the handler to coalesce rapid bursts of events into a
 * single deferred invocation.
 *
 * Setting an implementation on the Publisher is optional; without it, debounced
 * subscribers fall back to synchronous handling (backward compatible).
 */
interface DebounceHandlerInterface
{
    /**
     * Records an event occurrence for the given debounced subscriber, scheduling
     * (or rescheduling) its deferred invocation.
     *
     * @param DebouncedSubscriberInterface $subscriber
     * @param EventInterface $event
     */
    public function debounce(DebouncedSubscriberInterface $subscriber, EventInterface $event): void;
}
