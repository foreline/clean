<?php

declare(strict_types=1);

namespace Domain\Event;

/**
 *
 */
interface SubscriberInterface {

    /**
     * @param Event $event
     */
    public function handle(Event $event): void;
    //public function handle(EventInterface $event): void;

    /**
     * @param Event $event
     * @return bool
     */
    public function isSubscribedTo(Event $event): bool;
    //public function isSubscribedTo(EventInterface $event): bool;
}