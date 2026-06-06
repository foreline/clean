<?php
declare(strict_types=1);

namespace Domain\Event;

use InvalidArgumentException;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * Default {@see DebounceHandlerInterface} implementation.
 *
 * Responsibilities:
 *  - {@see debounce()} records/extends the debounce window for a subscriber in
 *    shared {@see DebounceStorageInterface} (called during Publisher::publish()).
 *  - {@see flush()} invokes every subscriber whose window has elapsed exactly
 *    once and clears its state (called periodically by a worker / scheduled task).
 *
 * Timing is injectable for deterministic testing, as is the strategy used to
 * resolve a subscriber instance from its class name.
 */
final class DebouncedSubscriberHandler implements DebounceHandlerInterface
{
    /** @var DebounceStorageInterface */
    private DebounceStorageInterface $storage;

    /** @var callable(class-string<SubscriberInterface>): SubscriberInterface */
    private $subscriberResolver;

    /** @var callable(): int */
    private $clock;

    /**
     * @param DebounceStorageInterface $storage
     * @param (callable(class-string<SubscriberInterface>): SubscriberInterface)|null $subscriberResolver
     *        Resolves a subscriber instance from its class name. Defaults to
     *        instantiating the class with a no-argument constructor.
     * @param (callable(): int)|null $clock
     *        Returns the current epoch time in milliseconds. Defaults to wall clock.
     */
    public function __construct(
        DebounceStorageInterface $storage,
        ?callable $subscriberResolver = null,
        ?callable $clock = null,
    ) {
        $this->storage = $storage;
        $this->subscriberResolver = $subscriberResolver
            ?? static fn(string $class): SubscriberInterface => new $class();
        $this->clock = $clock
            ?? static fn(): int => (int)(microtime(true) * 1000);
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException If the debounce window is not positive.
     */
    public function debounce(DebouncedSubscriberInterface $subscriber, EventInterface $event): void
    {
        $debounceMs = $subscriber->getDebounceMilliseconds();
        Assert::greaterThan($debounceMs, 0, 'Debounce window must be greater than zero milliseconds.');

        $maxWaitMs = $subscriber->getMaxWaitMilliseconds();
        Assert::greaterThanEq($maxWaitMs, 0, 'Max wait must be zero or greater.');

        $now = ($this->clock)();
        $key = $this->buildKey($subscriber, $event);

        $existing = $this->storage->get($key);
        $firstSeen = null !== $existing ? $existing->getFirstSeenAtMs() : $now;

        $deadline = 0 < $maxWaitMs ? $firstSeen + $maxWaitMs : 0;

        $dueAt = $now + $debounceMs;
        if ( 0 < $deadline && $dueAt > $deadline ) {
            $dueAt = $deadline;
        }

        $this->storage->save(
            new DebounceState(
                $key,
                $subscriber::class,
                $event::class,
                serialize($event),
                $dueAt,
                $firstSeen,
                $deadline,
            )
        );
    }

    /**
     * Invokes every due debounced subscriber exactly once and clears its state.
     *
     * Intended to be called repeatedly by a long-running worker or a scheduled
     * task. Each due bucket is re-read immediately before invocation so that a
     * window extended by a concurrent {@see debounce()} call is respected
     * (the bucket is skipped and flushed on a later run).
     *
     * @return int Number of subscribers invoked.
     */
    public function flush(): int
    {
        $now = ($this->clock)();
        $invoked = 0;

        foreach ( $this->storage->due($now) as $state ) {
            $fresh = $this->storage->get($state->getKey());

            // Bucket vanished or its window was extended past now — skip it.
            if ( null === $fresh || $fresh->getDueAtMs() > $now ) {
                continue;
            }

            if ( $this->invoke($fresh) ) {
                $invoked++;
            }

            $this->storage->delete($fresh->getKey());
        }

        return $invoked;
    }

    /**
     * Reconstructs and invokes the subscriber for a single state record.
     *
     * @param DebounceState $state
     * @return bool True if the subscriber was invoked.
     */
    private function invoke(DebounceState $state): bool
    {
        try {
            $subscriber = ($this->subscriberResolver)($state->getSubscriberClass());
            $event = unserialize($state->getSerializedEvent());
        } catch (Throwable) {
            return false;
        }

        if ( !$subscriber instanceof SubscriberInterface || !$event instanceof EventInterface ) {
            return false;
        }

        $subscriber->handle($event);

        return true;
    }

    /**
     * Builds the storage key, namespaced by subscriber class to avoid collisions.
     *
     * @param DebouncedSubscriberInterface $subscriber
     * @param EventInterface $event
     * @return string
     */
    private function buildKey(DebouncedSubscriberInterface $subscriber, EventInterface $event): string
    {
        return $subscriber::class . ':' . $subscriber->getDebounceKey($event);
    }
}
