<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\UseCase\ServiceInterface;

/**
 *
 */
interface SortInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    public function reset(): self;
    
    public function endSort(): ?ServiceInterface;
}