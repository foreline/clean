<?php
    namespace Domain\Event;

    use DateTimeImmutable;

    /**
     *
     */
    interface EventInterface {


        /**
         * @return DateTimeImmutable
         */
        public function occurredOn(): DateTimeImmutable;
    }