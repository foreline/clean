<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;

/**
 * Parent class for Aggregates
 */
abstract class AbstractManager extends AbstractValueObjectManager
{
    /**
     * @return IteratorInterface|null
     */
    //abstract public function find(): ?IteratorInterface;

    /**
     * @param int $id
     * @return AggregateInterface|null
     */
    //abstract public function findById(int $id): ?AggregateInterface;


    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
}
