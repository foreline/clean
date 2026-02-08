<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Dispatches events to async subscribers via an external transport.
 *
 * Implementations may use message queues (Redis, RabbitMQ),
 * database tables, or other persistence mechanisms.
 *
 * This interface keeps the domain layer transport-agnostic.
 */
interface AsyncDispatcherInterface
{
    /**
     * Dispatches an event for asynchronous processing by the given subscriber.
     *
     * @param EventInterface $event The domain event to process
     * @param AsyncSubscriberInterface $subscriber The subscriber to handle the event
     */
    public function dispatch(EventInterface $event, AsyncSubscriberInterface $subscriber): void;
}
