<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Service\ServiceInterface;

/**
 * Repository Interface
 */
interface RepositoryInterface
{
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null);
    
    /**
     * @return array<string, string>
     */
    public static function getFields(): array;

    /**
     * @param AggregateInterface $entity
     * @return AggregateInterface
     */
    // @fixme Uncomment interface method
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
     * Количество записей, попавших под выборку
     * @return int
     */
    public function getCount(): int;
    
    /**
     * Всего записей
     * @return int
     */
    public function getTotalCount(): int;
}