<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use Domain\Entity\EntityInterface;

/**
 * Aggregate Interface
 */
interface AggregateInterface extends EntityInterface, ConvertableInterface
{

}