<?php
    declare(strict_types=1);
    
    namespace Domain\Event\Infrastructure\Repository;
    
    use Domain\Event\EventStore;

    interface EventStoreRepositoryInterface
    {
        /**
         * @param int $id
         * @return EventStore
         */
        public function findById(int $id): EventStore;
    
        /**
         * @return ?EventStore[]
         */
        public function find(): ?array;
        
    }