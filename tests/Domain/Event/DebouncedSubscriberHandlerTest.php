<?php
declare(strict_types=1);

namespace Tests\Domain\Event;

use Domain\Event\DebounceState;
use Domain\Event\DebounceStorageInterface;
use Domain\Event\DebouncedSubscriberHandler;
use Domain\Event\DebouncedSubscriberInterface;
use Domain\Event\EventInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Domain\Event\DebouncedSubscriberHandler
 * @covers \Domain\Event\DebounceState
 */
class DebouncedSubscriberHandlerTest extends TestCase
{
    private InMemoryDebounceStorage $storage;

    /** @var int Controllable clock value in milliseconds. */
    private int $now;

    protected function setUp(): void
    {
        $this->storage = new InMemoryDebounceStorage();
        $this->now = 1_000_000;
    }

    private function makeHandler(?callable $resolver = null): DebouncedSubscriberHandler
    {
        return new DebouncedSubscriberHandler(
            $this->storage,
            $resolver,
            fn(): int => $this->now,
        );
    }

    public function testDebounceSchedulesDueAtNowPlusWindow(): void
    {
        // Arrange
        $handler = $this->makeHandler();
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000);
        $event = new DebounceTestEvent('u1');

        // Act
        $handler->debounce($subscriber, $event);

        // Assert
        $state = $this->storage->get(SpyDebouncedSubscriber::class . ':badge:u1');
        $this->assertNotNull($state);
        $this->assertSame($this->now + 2000, $state->getDueAtMs());
        $this->assertSame($this->now, $state->getFirstSeenAtMs());
    }

    public function testRepeatedDebounceExtendsWindowButKeepsFirstSeen(): void
    {
        // Arrange
        $handler = $this->makeHandler();
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000);
        $event = new DebounceTestEvent('u1');
        $handler->debounce($subscriber, $event);
        $firstSeen = $this->now;

        // Act — a second event arrives 1s later, before the window elapsed.
        $this->now += 1000;
        $handler->debounce($subscriber, $event);

        // Assert — due time pushed back, first-seen unchanged (trailing edge).
        $state = $this->storage->get(SpyDebouncedSubscriber::class . ':badge:u1');
        $this->assertNotNull($state);
        $this->assertSame($this->now + 2000, $state->getDueAtMs());
        $this->assertSame($firstSeen, $state->getFirstSeenAtMs());
    }

    public function testMaxWaitCapsTheDueTime(): void
    {
        // Arrange — 2s debounce but never wait more than 3s total.
        $handler = $this->makeHandler();
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000, maxWaitMs: 3000);
        $event = new DebounceTestEvent('u1');
        $firstSeen = $this->now;
        $handler->debounce($subscriber, $event);

        // Act — continuous events keep arriving every 1s.
        $this->now += 1000;
        $handler->debounce($subscriber, $event); // would be now+2000 = firstSeen+3000 (at cap)
        $this->now += 1000;
        $handler->debounce($subscriber, $event); // would exceed cap -> clamped

        // Assert — due never exceeds firstSeen + maxWait.
        $state = $this->storage->get(SpyDebouncedSubscriber::class . ':badge:u1');
        $this->assertNotNull($state);
        $this->assertSame($firstSeen + 3000, $state->getDueAtMs());
    }

    public function testFlushInvokesDueSubscriberOnceAndClearsState(): void
    {
        // Arrange
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000);
        $handler = $this->makeHandler(fn(string $class): object => $subscriber);
        $handler->debounce($subscriber, new DebounceTestEvent('u1'));

        // Act — advance past the window, then flush.
        $this->now += 2001;
        $invoked = $handler->flush();

        // Assert
        $this->assertSame(1, $invoked);
        $this->assertSame(1, $subscriber->handledCount);
        $this->assertNull($this->storage->get(SpyDebouncedSubscriber::class . ':badge:u1'));
    }

    public function testFlushCoalescesBurstIntoSingleInvocation(): void
    {
        // Arrange — 5 events within the window.
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000);
        $handler = $this->makeHandler(fn(string $class): object => $subscriber);
        for ( $i = 0; $i < 5; $i++ ) {
            $this->now += 100;
            $handler->debounce($subscriber, new DebounceTestEvent('u1'));
        }

        // Act — flush before the window elapses: nothing is due yet.
        $earlyFlush = $handler->flush();

        // Advance past the window and flush again.
        $this->now += 2001;
        $lateFlush = $handler->flush();

        // Assert
        $this->assertSame(0, $earlyFlush);
        $this->assertSame(1, $lateFlush);
        $this->assertSame(1, $subscriber->handledCount);
    }

    public function testFlushSkipsNotYetDueState(): void
    {
        // Arrange
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 5000);
        $handler = $this->makeHandler(fn(string $class): object => $subscriber);
        $handler->debounce($subscriber, new DebounceTestEvent('u1'));

        // Act — flush before window elapses.
        $this->now += 1000;
        $invoked = $handler->flush();

        // Assert
        $this->assertSame(0, $invoked);
        $this->assertSame(0, $subscriber->handledCount);
        $this->assertNotNull($this->storage->get(SpyDebouncedSubscriber::class . ':badge:u1'));
    }

    public function testSeparateKeysAreDebouncedIndependently(): void
    {
        // Arrange
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 2000);
        $handler = $this->makeHandler(fn(string $class): object => $subscriber);
        $handler->debounce($subscriber, new DebounceTestEvent('u1'));
        $handler->debounce($subscriber, new DebounceTestEvent('u2'));

        // Act
        $this->now += 2001;
        $invoked = $handler->flush();

        // Assert — one invocation per distinct key.
        $this->assertSame(2, $invoked);
        $this->assertSame(2, $subscriber->handledCount);
    }

    public function testZeroDebounceWindowIsRejected(): void
    {
        // Arrange
        $handler = $this->makeHandler();
        $subscriber = new SpyDebouncedSubscriber(debounceMs: 0);

        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        $handler->debounce($subscriber, new DebounceTestEvent('u1'));
    }
}

/**
 * Simple in-memory storage double for deterministic handler tests.
 */
class InMemoryDebounceStorage implements DebounceStorageInterface
{
    /** @var array<string, DebounceState> */
    private array $states = [];

    public function save(DebounceState $state): void
    {
        $this->states[$state->getKey()] = $state;
    }

    public function get(string $key): ?DebounceState
    {
        return $this->states[$key] ?? null;
    }

    public function delete(string $key): void
    {
        unset($this->states[$key]);
    }

    public function due(int $nowMs): array
    {
        return array_values(array_filter(
            $this->states,
            static fn(DebounceState $state): bool => $state->getDueAtMs() <= $nowMs,
        ));
    }
}

/**
 * Debounced subscriber spy that counts handle() invocations.
 */
class SpyDebouncedSubscriber implements DebouncedSubscriberInterface
{
    public int $handledCount = 0;

    public function __construct(
        private int $debounceMs = 2000,
        private int $maxWaitMs = 0,
    ) {
    }

    public function handle(EventInterface $event): void
    {
        $this->handledCount++;
    }

    public function isSubscribedTo(EventInterface $event): bool
    {
        return $event instanceof DebounceTestEvent;
    }

    public function getDebounceKey(EventInterface $event): string
    {
        /** @var DebounceTestEvent $event */
        return 'badge:' . $event->getUserId();
    }

    public function getDebounceMilliseconds(): int
    {
        return $this->debounceMs;
    }

    public function getMaxWaitMilliseconds(): int
    {
        return $this->maxWaitMs;
    }
}

/**
 * Test event carrying a user identifier used as the debounce key.
 */
class DebounceTestEvent implements EventInterface
{
    public function __construct(private string $userId)
    {
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}
