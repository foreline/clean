<?php
declare(strict_types=1);

namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Aggregate\PostCollection;
use App\Domain\Post\Repository\PostRepositoryInterface;

/**
 * Post Manager - Handles basic CRUD operations
 */
class PostManager
{
    private PostRepositoryInterface $repository;

    public function __construct(?PostRepositoryInterface $repository = null)
    {
        // You should use a dependency injection container
        $this->repository = $repository ?? new \App\Infrastructure\Post\Repository\PostRepository();
    }

    public function persist(Post $post): Post
    {
        return $this->repository->persist($post);
    }

    public function findById(int $id): ?Post
    {
        return $this->repository->findById($id);
    }

    public function find(): ?PostCollection
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
