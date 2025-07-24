<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\UseCase;

use App\Domain\Taxonomy\Aggregate\Category;
use App\Domain\Taxonomy\Aggregate\CategoryCollection;
use App\Domain\Taxonomy\Repository\CategoryRepositoryInterface;

/**
 * Category Manager - Handles basic CRUD operations
 */
class CategoryManager
{
    private CategoryRepositoryInterface $repository;

    public function __construct(?CategoryRepositoryInterface $repository = null)
    {
        // You should use a dependency injection container
        // For now using a placeholder - will create Infrastructure layer later
        $this->repository = $repository ?? throw new \Exception('CategoryRepository not implemented yet');
    }

    public function persist(Category $category): Category
    {
        return $this->repository->persist($category);
    }

    public function findById(int $id): ?Category
    {
        return $this->repository->findById($id);
    }

    public function find(): ?CategoryCollection
    {
        return $this->repository->find();
    }

    public function findByName(string $name): ?Category
    {
        return $this->repository->findByName($name);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
