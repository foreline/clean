<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 *
 */
interface FilterInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    public function reset(): static;
    
    public function endFilter(): ?ServiceInterface;
    
    public function byCondition(): ?ConditionFilterInterface;
}