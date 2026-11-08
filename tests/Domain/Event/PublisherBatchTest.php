<?php
declare(strict_types=1);

namespace Tests\Domain\Event;

use DateTimeImmutable;
use Domain\Event\AsyncEnqueueFilterInterface;
use Domain\Event\AsyncSubscriberInterface;
use Domain\Event\BatchAsyncDispatcherInterface;
use Domain\Event\EventInterface;
use Domain\Event\Publisher;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

/** @covers \Domain\Event\Publisher */
final class PublisherBatchTest extends TestCase
{
    protected function setUp(): void
    {
        (new ReflectionProperty(Publisher::class, 'instance'))->setValue(null, null);
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(Publisher::class, 'instance'))->setValue(null, null);
    }

    public function testPublishesAllInterestedSubscribersTogetherInPriorityOrder(): void
    {
        $publisher = Publisher::getInstance();
        $dispatcher = new RecordingBatchDispatcher();
        $publisher->setAsyncDispatcher($dispatcher);
        $low = new BatchSubscriber('low', true);
        $high = new BatchSubscriber('high', true);
        $skip = new BatchSubscriber('skip', false);
        $publisher->subscribe($low);
        $publisher->subscribe($high, 10);
        $publisher->subscribe($skip, 20);
        $event = new BatchEvent();
        $publisher->publish($event);
        self::assertSame([[$event, [$high, $low]]], $dispatcher->batches);
        self::assertSame(0, $dispatcher->singleCalls);
    }

    public function testEmptyAndRepeatedPublicationsDoNotShareDeliveryState(): void
    {
        $publisher = Publisher::getInstance();
        $dispatcher = new RecordingBatchDispatcher();
        $publisher->setAsyncDispatcher($dispatcher);
        $publisher->subscribe(new BatchSubscriber('skip', false));
        $publisher->publish(new BatchEvent());
        self::assertSame([], $dispatcher->batches);
        $subscriber = new BatchSubscriber('yes', true);
        $publisher->subscribe($subscriber);
        $first = new BatchEvent();
        $second = new BatchEvent();
        $publisher->publish($first, $second);
        self::assertSame([[$first, [$subscriber]], [$second, [$subscriber]]], $dispatcher->batches);
    }
}

final class BatchEvent implements EventInterface
{
    public function occurredOn(): DateTimeImmutable { return new DateTimeImmutable(); }
}

final class BatchSubscriber implements AsyncSubscriberInterface, AsyncEnqueueFilterInterface
{
    public function __construct(private string $name, private bool $interested) {}
    public static function getSubscribedEvents(): array { return [BatchEvent::class]; }
    public function shouldEnqueue(EventInterface $event): bool { return $this->interested; }
    public function isSubscribedTo(EventInterface $event): bool { return $event instanceof BatchEvent; }
    public function isHandled(EventInterface $event): bool { return true; }
    public function handle(EventInterface $event): void { throw new \LogicException('Must be queued'); }
}

final class RecordingBatchDispatcher implements BatchAsyncDispatcherInterface
{
    public array $batches = [];
    public int $singleCalls = 0;
    public function dispatch(EventInterface $event, AsyncSubscriberInterface $subscriber): void { $this->singleCalls++; }
    public function dispatchBatch(EventInterface $event, array $subscribers): void { $this->batches[] = [$event, $subscribers]; }
}
