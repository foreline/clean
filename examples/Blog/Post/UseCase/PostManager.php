<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Aggregate\PostCollection;
use App\Domain\Post\Repository\PostRepositoryInterface;

/**
 * Post Manager - Handles basic CRUD operations
 * 
 * This class demonstrates proper dependency injection for EntityManager classes.
 * The repository interface is injected via constructor, maintaining Clean Architecture
 * by depending on abstractions rather than concrete implementations.
 */
class PostManager
{
    private PostRepositoryInterface $repository;

    /**
     * @param PostRepositoryInterface $repository Repository implementation will be
     *                                           injected by DI container based on
     *                                           configuration bindings
     */
    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function persist(Post $post): Post
    {
        return $this->repository->persist($post);
    }

    public function findById(int $id): ?Post
    {
        return $this->repository->findById($id);
    }

    public function findAll(): ?PostCollection
    {
        return $this->repository->find();
    }

    public function findByAuthor(int $authorId): ?PostCollection
    {
        return $this->repository->findByAuthor($authorId);
    }

    public function findByCategory(int $categoryId): ?PostCollection
    {
        return $this->repository->findByCategory($categoryId);
    }

    public function findByTag(int $tagId): ?PostCollection
    {
        return $this->repository->findByTag($tagId);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
    }
}
