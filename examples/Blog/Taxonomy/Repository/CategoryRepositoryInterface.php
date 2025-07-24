<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Repository;

use App\Domain\Taxonomy\Aggregate\Category;
use App\Domain\Taxonomy\Aggregate\CategoryCollection;

/**
 * Category Repository Interface
 */
interface CategoryRepositoryInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';

    /**
     * @param Category $category
     * @return Category
     */
    public function persist(Category $category): Category;

    /**
     * @param int $id
     * @return ?Category
     */
    public function findById(int $id): ?Category;

    /**
     * @return ?CategoryCollection
     */
    public function find(): ?CategoryCollection;

    /**
     * @param string $name
     * @return ?Category
     */
    public function findByName(string $name): ?Category;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
