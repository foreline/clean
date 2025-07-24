<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\UseCase;

use App\Domain\Taxonomy\Aggregate\Tag;
use App\Domain\Taxonomy\Aggregate\TagCollection;
use App\Domain\Taxonomy\Repository\TagRepositoryInterface;

/**
 * Tag Manager - Handles basic CRUD operations
 */
class TagManager
{
    private TagRepositoryInterface $repository;

    public function __construct(?TagRepositoryInterface $repository = null)
    {
        // You should use a dependency injection container
        // For now using a placeholder - will create Infrastructure layer later
        $this->repository = $repository ?? throw new \Exception('TagRepository not implemented yet');
    }

    public function persist(Tag $tag): Tag
    {
        return $this->repository->persist($tag);
    }

    public function findById(int $id): ?Tag
    {
        return $this->repository->findById($id);
    }

    public function find(): ?TagCollection
    {
        return $this->repository->find();
    }

    public function findByName(string $name): ?Tag
    {
        return $this->repository->findByName($name);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
