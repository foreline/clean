<?php

namespace Domain\Aggregate;

/**
 * Interface for sortable aggregates
 */
interface SortableInterface
{
    public function setSort(): self;
    public function getSort(): int;
}