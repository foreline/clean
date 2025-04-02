<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * Interface for sortable aggregates
 */
interface SortableInterface
{
    /**
     * @param int $sort
     * @return $this
     */
    public function setSort(int $sort): self;
    
    /**
     * @return int
     */
    public function getSort(): int;
}