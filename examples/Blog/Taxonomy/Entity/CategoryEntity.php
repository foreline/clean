<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Entity;

use Domain\Entity\EntityInterface;

/**
 * Category Entity - Simple data container
 */
class CategoryEntity implements EntityInterface
{
    private ?int $id = null;
    private string $name = '';
    private string $description = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
