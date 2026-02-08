<?php

declare(strict_types=1);

namespace Domain\Event;

/**
 *
 */
interface SubscriberInterface
{
    /**
     * @param Event $event
     */
    public function handle(Event $event): void;
    //public function handle(EventInterface $event): void;
    
    /**
     * @param Event|EventInterface $event
     * @return bool
     */
    public function isSubscribedTo(Event|EventInterface $event): bool;
    //public function isSubscribedTo(EventInterface $event): bool;
    
    /**
     * Checks the criteria for whether an event should be processed.
     * @param Event $event
     * @return bool
     */
    //public function isHandled(Event $event): bool;
}