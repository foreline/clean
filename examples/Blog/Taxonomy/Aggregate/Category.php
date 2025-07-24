<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Aggregate;

use App\Domain\Taxonomy\Entity\CategoryEntity;
use Domain\Aggregate\AggregateInterface;

/**
 * Category Aggregate
 */
class Category extends CategoryEntity implements AggregateInterface
{
    /**
     * Business rule: Check if category name is valid
     */
    public function hasValidName(): bool
    {
        return !empty(trim($this->getName()));
    }

    /**
     * Business rule: Generate slug from name
     */
    public function generateSlug(): string
    {
        return strtolower(str_replace(' ', '-', trim($this->getName())));
    }
}
