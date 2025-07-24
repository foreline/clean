<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Entity;

use Domain\Entity\EntityInterface;

/**
 * Tag Entity - Simple data container
 */
class TagEntity implements EntityInterface
{
    private ?int $id = null;
    private string $name = '';
    private string $color = '#000000';

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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }
}
