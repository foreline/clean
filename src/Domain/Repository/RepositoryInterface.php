<?php
    declare(strict_types=1);
    
    namespace Domain\Repository;

    use Domain\Aggregate\AggregateInterface;
    use Domain\Aggregate\IteratorInterface;

    /**
     * Repository Interface
     */
    interface RepositoryInterface
    {
    
        /**
         * @return array<string, string>
         */
        public static function getFields(): array;
    
        /**
         * @param AggregateInterface $entity
         * @return AggregateInterface
         */
        // @fixme
        //public function persist(AggregateInterface $entity): AggregateInterface;

        /**
         * @param int $id
         * @return bool
         */
        public function delete(int $id): bool;

        /**
         * @return ?AggregateInterface[]
         */
        public function find(): ?IteratorInterface;
    
        /**
         * @param int $id
         * @return ?AggregateInterface
         */
        public function findById(int $id): ?AggregateInterface;
    
        /**
         * @return void
         */
        public function startTransaction(): void;
    
        /**
         * @return void
         */
        public function commitTransaction(): void;
    
        /**
         * @return void
         */
        public function rollbackTransaction(): void;
    
        /**
         * @return int
         */
        public function getTotalCount(): int;
    }