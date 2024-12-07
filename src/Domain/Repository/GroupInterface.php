<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

interface GroupInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    /**
     * Сбрасывает группируемые поля
     * @return $this
     */
    public function reset(): self;
    
    public function endGroup(): ?ServiceInterface;
}