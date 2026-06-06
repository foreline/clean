<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Subscriber whose handling is debounced (coalesced) across multiple events.
 *
 * When many events of interest are published in quick succession, a debounced
 * subscriber is NOT invoked once per event. Instead, the invocation is deferred
 * until a quiet period (the debounce window) has elapsed since the last matching
 * event, at which point {@see SubscriberInterface::handle()} is called a single
 * time with the most recent event.
 *
 * This prevents redundant, expensive work (e.g. recomputing a notification badge
 * count) from running dozens of times per second during burst operations.
 *
 * Debouncing is coordinated through a shared {@see DebounceStorageInterface},
 * so it works reliably across independent PHP requests/processes. The actual
 * deferred invocation is performed by {@see DebouncedSubscriberHandler::flush()},
 * which is expected to run periodically from a worker or scheduled task.
 *
 * Requires a {@see DebounceHandlerInterface} to be set on the Publisher.
 * If none is configured, the subscriber falls back to synchronous handling
 * (backward compatible — every event is handled immediately).
 *
 * @example
 *   class BadgeCountSubscriber implements DebouncedSubscriberInterface
 *   {
 *       public function getDebounceKey(EventInterface $event): string
 *       {
 *           // One coalesced invocation per user.
 *           return 'badge:' . $event->getUserId();
 *       }
 *
 *       public function getDebounceMilliseconds(): int
 *       {
 *           return 2000; // Recompute at most once per 2s of silence.
 *       }
 *
 *       public function getMaxWaitMilliseconds(): int
 *       {
 *           return 10000; // But never wait more than 10s during a continuous burst.
 *       }
 *       // handle() / isSubscribedTo() ...
 *   }
 */
interface DebouncedSubscriberInterface extends SubscriberInterface
{
    /**
     * Returns the debounce bucket key for the given event.
     *
     * Events sharing the same key are coalesced into a single deferred
     * invocation. Use a stable, low-cardinality identifier (e.g. a user id,
     * a tenant id, or a constant for a global counter).
     *
     * The key is namespaced by the subscriber class internally, so it only
     * needs to be unique within this subscriber.
     *
     * @param EventInterface $event
     * @return string
     */
    public function getDebounceKey(EventInterface $event): string;

    /**
     * Returns the debounce window in milliseconds.
     *
     * The deferred invocation fires once no matching event has been seen for
     * this duration (trailing-edge debounce). Must be greater than zero.
     *
     * @return int
     */
    public function getDebounceMilliseconds(): int;

    /**
     * Returns the maximum time, in milliseconds, an invocation may be deferred
     * while events keep arriving (guards against starvation during an unending
     * burst).
     *
     * Return 0 for no cap (pure trailing-edge debounce).
     *
     * @return int
     */
    public function getMaxWaitMilliseconds(): int;
}
