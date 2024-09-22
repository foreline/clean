<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\UseCase\ServiceInterface;

/**
 *
 */
interface LimitInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    public function reset(): self;
    
    public function endLimit(): ?ServiceInterface;
}