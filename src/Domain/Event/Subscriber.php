<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Event subscription
 */
class Subscriber implements SubscriberInterface
{
    public EventStore $eventStore;

    /**
     */
    public function __construct(/*EventStore $eventStore*/)
    {
        $this->eventStore = EventStore::getInstance();
    }

    /**
     * @param Event $event
     */
    public function handle(Event $event): void
    {
        $this->eventStore->append($event);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function isSubscribedTo(Event $event): bool
    {
        return true;
    }
}