<?php
    declare(strict_types=1);
    
    namespace Domain\Subscriber;
    
    use Domain\Event\Event;
    use Domain\Event\SubscriberInterface;
    use Domain\Events\ExceptionOccurredEvent;
    use Throwable;

    /**
     * Обработчик события вызова исключения при тестировании
     */
    class TestsExceptionSubscriber implements SubscriberInterface
    {
        /**
         * @param ExceptionOccurredEvent $event
         * @return void
         * @throws Throwable
         */
        public function handle(Event $event): void
        {
            throw $event->getException();
        }
    
        /**
         * @param ExceptionOccurredEvent $event
         * @return bool
         */
        public function isSubscribedTo(Event $event): bool
        {
            return $event instanceof ExceptionOccurredEvent;
        }
    }