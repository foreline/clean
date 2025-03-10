<?php

namespace Domain\Aggregate;

/**
 * Interface for sortable aggregates
 */
interface SortableInterface
{
    public function setSort(int $sort): self;
    public function getSort(): int;
}