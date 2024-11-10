<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
interface ConditionFilterInterface
{
    public function __construct(FilterInterface $filter);
    public function endCondition(): FilterInterface;
}