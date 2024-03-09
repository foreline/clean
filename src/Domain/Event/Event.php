<?php
    declare(strict_types=1);
    
    namespace Domain\Event;

    use DateTimeImmutable;

    /**
     * Domain Event
     */
    class Event implements EventInterface
    {
        private DateTimeImmutable $occurredOn;
    
        /**
         *
         */
        public function __construct()
        {
            $this->occurredOn = new DateTimeImmutable();
        }
    
        /**
         * @return DateTimeImmutable
         */
        public function occurredOn(): DateTimeImmutable
        {
            return $this->occurredOn;
        }
    }