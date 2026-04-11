<?php

declare(strict_types=1);

namespace Domain\Event;

/**
 *
 */
interface SubscriberInterface
{
    /**
     * @param EventInterface $event
     */
    public function handle(EventInterface $event): void;
    
    /**
     * @param EventInterface $event
     * @return bool
     */
    public function isSubscribedTo(EventInterface $event): bool;
    
    /**
     * Checks the criteria for whether an event should be processed.
     * @param Event $event
     * @return bool
     */
    //public function isHandled(EventInterface $event): bool;
}