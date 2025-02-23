<?php
declare(strict_types=1);

namespace Domain\Entity;

use Domain\Aggregate\AggregateInterface;
use Domain\ValueObject\ValueObjectInterface;

/**
 *
 */
interface FromArrayInterface
{
    /**
     * Restores entity from array presentation
     * @param array $data
     * @return AggregateInterface|ValueObjectInterface
     */
    public function fromArray(array $data = []): AggregateInterface|ValueObjectInterface;
}