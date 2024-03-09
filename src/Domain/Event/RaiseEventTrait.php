<?php
    declare(strict_types=1);
    
    namespace Domain\Event;

    /**
     *
     */
    trait RaiseEventTrait {

        protected array $events = [];

        /**
         * @return array
         */
        public function flush(): array
        {
            $events = $this->events;
            $this->events = [];
            return $events;
        }

        /**
         * @param $event
         */
        protected function raise($event): void
        {
            $this->events[] = $event;
        }
    }