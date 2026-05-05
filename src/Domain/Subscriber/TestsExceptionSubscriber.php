<?php
declare(strict_types=1);

namespace Domain\Subscriber;

use Domain\Event\EventInterface;
use Domain\Event\IndexedSubscriberInterface;
use Domain\Events\ExceptionOccurredEvent;
use Throwable;

/**
 * Exception Event handler while PHPUnit test
 */
class TestsExceptionSubscriber implements IndexedSubscriberInterface
{
    /**
     * Returns the list of event class names this subscriber handles.
     * Must include every event class that isSubscribedTo() would return true for.
     *
     * @return array<class-string<EventInterface>>
     */
    public static function getSubscribedEvents(): array
    {
        return [ExceptionOccurredEvent::class];
    }
    
    /**
     * @param ExceptionOccurredEvent $event
     * @return void
     * @throws Throwable
     */
    public function handle(EventInterface $event): void
    {
        throw $event->getException();
    }

    /**
     * @param ExceptionOccurredEvent $event
     * @return bool
     */
    public function isSubscribedTo(EventInterface $event): bool
    {
        return $event instanceof ExceptionOccurredEvent;
    }
}