<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Aggregate;

use App\Domain\Taxonomy\Entity\TagEntity;
use Domain\Aggregate\AggregateInterface;

/**
 * Tag Aggregate
 */
class Tag extends TagEntity implements AggregateInterface
{
    /**
     * Business rule: Check if tag name is valid
     */
    public function hasValidName(): bool
    {
        return !empty(trim($this->getName()));
    }

    /**
     * Business rule: Validate color format
     */
    public function hasValidColor(): bool
    {
        return preg_match('/^#[a-fA-F0-9]{6}$/', $this->getColor()) === 1;
    }

    /**
     * Business rule: Generate slug from name
     */
    public function generateSlug(): string
    {
        return strtolower(str_replace(' ', '-', trim($this->getName())));
    }
}
