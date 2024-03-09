<?php
    declare(strict_types=1);
    
    namespace Domain\Event;

    /**
     * Domain Events Storage
     */
    class EventStore
    {
        /** @var Event[]  */
        public array $events = [];
        
        /** @var ?self  */
        public static ?self $instance = null;

        /**
         *
         */
        private function __construct(){

        }

        /**
         * @return self
         */
        public static function getInstance(): self
        {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * @param EventInterface $event
         */
        public function append(EventInterface $event): void
        {
            $storedEvent = new StoredEventInterface(
                get_class($event),
                $event->occurredOn(),
                $this->serialize($event)
            );

            $this->events[] = $storedEvent;

            //...->persist($storedEvent);
        }

        /**
         * @return EventInterface[]|null
         */
        public function find(): ?array
        {
            $events = [];

            while ( $event = $this->fetch() ) {
                $events[] = $event;
            }

            return $events;
        }

        /**
         * @param int $id
         * @return EventInterface|null
         */
        public function findById(int $id): ?EventInterface
        {
            if ( !$events = $this->find(['id' => $id]) ) {
                return null;
            }

            return $events[0];
        }

        /**
         * @return EventInterface|null
         */
        public function fetch(): ?EventInterface
        {
            if ( null === $this->result ) {
                return null;
            }

            $arEvent = $this->result->fetch();

            //$event = new Event();

            return $event;
        }

        /**
         * @param EventInterface $event
         * @return string
         */
        private function serialize(EventInterface $event): string
        {
            return serialize($event);
        }

        /**
         * @param string $data
         * @return EventInterface
         */
        private function unserialize(string $data): EventInterface
        {
            return unserialize($data, ['allowed_classes' => ['DomainEvent']]);
        }

        /**
         * @return array
         */
        public function getEvents(): array
        {
            return $this->events;
        }

    }