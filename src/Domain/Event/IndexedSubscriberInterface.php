<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Subscriber that declares its event types at registration time,
 * enabling O(1) dispatch via the Publisher's event index.
 *
 * Implement this interface alongside SubscriberInterface to opt in to
 * indexed dispatch. The Publisher will route events directly to this
 * subscriber without calling isSubscribedTo() during publish().
 *
 * isSubscribedTo() must still be implemented correctly — it is used by
 * getEventSubscribers() and other introspection methods.
 *
 * @example
 *   class EntityPersistedSubscriber implements IndexedSubscriberInterface
 *   {
 *       public static function getSubscribedEvents(): array
 *       {
 *           return [EntityCreatedEvent::class, EntityUpdatedEvent::class];
 *       }
 *       // ...
 *   }
 *
 *   // Priority is passed at the registration call site:
 *   Publisher::getInstance()->subscribe(new EntityPersistedSubscriber(), priority: -50);
 */
interface IndexedSubscriberInterface extends SubscriberInterface
{
    /**
     * Returns the list of event class names this subscriber handles.
     * Must include every event class that isSubscribedTo() would return true for.
     *
     * @return array<class-string<EventInterface>>
     */
    public static function getSubscribedEvents(): array;
}
