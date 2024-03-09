<?php
    declare(strict_types=1);
    
    namespace Domain\Event;

    use DateTimeImmutable;

    /**
     *
     */
    interface EventInterface
    {
        /**
         * @return DateTimeImmutable
         */
        public function occurredOn(): DateTimeImmutable;
    }