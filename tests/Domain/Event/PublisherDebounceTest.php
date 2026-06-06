<?php
declare(strict_types=1);

namespace Tests\Domain\Event;

use Domain\Event\DebounceHandlerInterface;
use Domain\Event\DebouncedSubscriberInterface;
use Domain\Event\EventInterface;
use Domain\Event\Publisher;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/**
 * @covers \Domain\Event\Publisher
 */
class PublisherDebounceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->resetPublisher();
    }

    protected function tearDown(): void
    {
        $this->resetPublisher();
    }

    /**
     * Resets the Publisher singleton for full test isolation.
     */
    private function resetPublisher(): void
    {
        $property = new ReflectionProperty(Publisher::class, 'instance');
        $property->setAccessible(true);
        $property->setValue(null, null);
    }

    public function testDebouncedSubscriberIsRoutedToHandler(): void
    {
        // Arrange
        $handler = new RecordingDebounceHandler();
        $subscriber = new CountingDebouncedSubscriber();

        $publisher = Publisher::getInstance();
        $publisher->setDebounceHandler($handler);
        $publisher->subscribe($subscriber);

        $event = new PublisherDebounceTestEvent();

        // Act
        $publisher->publish($event);

        // Assert — handler received it, subscriber not invoked directly.
        $this->assertSame(1, $handler->debounceCount);
        $this->assertSame(0, $subscriber->handledCount);
    }

    public function testWithoutHandlerDebouncedSubscriberFallsBackToSync(): void
    {
        // Arrange — no debounce handler configured.
        $subscriber = new CountingDebouncedSubscriber();

        $publisher = Publisher::getInstance();
        $publisher->subscribe($subscriber);

        // Act
        $publisher->publish(new PublisherDebounceTestEvent());

        // Assert — handled synchronously (backward compatible).
        $this->assertSame(1, $subscriber->handledCount);
    }
}

/**
 * Debounce handler that records how many times debounce() was called.
 */
class RecordingDebounceHandler implements DebounceHandlerInterface
{
    public int $debounceCount = 0;

    public function debounce(DebouncedSubscriberInterface $subscriber, EventInterface $event): void
    {
        $this->debounceCount++;
    }
}

/**
 * Debounced subscriber counting direct handle() calls.
 */
class CountingDebouncedSubscriber implements DebouncedSubscriberInterface
{
    public int $handledCount = 0;

    public function handle(EventInterface $event): void
    {
        $this->handledCount++;
    }

    public function isSubscribedTo(EventInterface $event): bool
    {
        return $event instanceof PublisherDebounceTestEvent;
    }

    public function getDebounceKey(EventInterface $event): string
    {
        return 'global';
    }

    public function getDebounceMilliseconds(): int
    {
        return 1000;
    }

    public function getMaxWaitMilliseconds(): int
    {
        return 0;
    }
}

/**
 * Event used by the Publisher debounce integration tests.
 */
class PublisherDebounceTestEvent implements EventInterface
{
    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
