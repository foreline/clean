<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * Marker interface for collections of entities that can be sourced from multiple origins.
 */
interface MultiSourceInterface
{
    public function getPrimary(): ?SourceHolderInterface;
    
}
