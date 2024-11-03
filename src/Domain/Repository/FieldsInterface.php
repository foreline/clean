<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 *
 */
interface FieldsInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    public function reset(): self;
    
    public function endFields(): ?ServiceInterface;
}