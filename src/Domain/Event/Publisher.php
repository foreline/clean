<?php
    declare(strict_types=1);
    
    namespace Domain\Event;

    /**
     * Подписка и публикация событий
     */
    class Publisher
    {
        /** @var Subscriber[] */
        private array $subscribers;
        private static ?self $instance = null;

        /**
         *
         */
        private function __construct()
        {
            $this->subscribers = [];
        }

        /**
         * @return Publisher
         */
        public static function getInstance(): self
        {
            if ( null === self::$instance ) {
                self::$instance = (new self());
            }

            return static::$instance;
        }

        /**
         * Подписка на событие
         * @param Subscriber $subscriber
         */
        public function subscribe(SubscriberInterface $subscriber): void
        {
            if ( !in_array($subscriber, $this->subscribers, false)) {
                $this->subscribers[] = $subscriber;
            }
        }
    
        /**
         * The publish method checks all possible subscribers to see if they are interested in the published domain event.
         * If so, the subscriber's handle method is called.
         * @param Event ...$events
         */
        public function publish(EventInterface ... $events): void
        {
            foreach ( $this->subscribers as $subscriber ) {
                foreach ( $events as $event ) {
                    if ( $subscriber->isSubscribedTo($event) ) {
                        $subscriber->handle($event);
                    }
                }
            }
        }

        /**
         * @return array
         */
        public function getSubscribers(): array
        {
            return $this->subscribers;
        }
    }