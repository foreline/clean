<?php
declare(strict_types=1);

namespace App\Domain\Comment\UseCase;

use App\Domain\Comment\Aggregate\Comment;
use App\Domain\Comment\Aggregate\CommentCollection;
use App\Domain\Comment\Repository\CommentRepositoryInterface;

/**
 * Comment Manager - Handles basic CRUD operations
 */
class CommentManager
{
    private CommentRepositoryInterface $repository;

    public function __construct(?CommentRepositoryInterface $repository = null)
    {
        // You should use a dependency injection container
        // For now using a placeholder - will create Infrastructure layer later
        $this->repository = $repository ?? throw new \Exception('CommentRepository not implemented yet');
    }

    public function persist(Comment $comment): Comment
    {
        return $this->repository->persist($comment);
    }

    public function findById(int $id): ?Comment
    {
        return $this->repository->findById($id);
    }

    public function find(): ?CommentCollection
    {
        return $this->repository->find();
    }

    public function findByPost(int $postId): ?CommentCollection
    {
        return $this->repository->findByPost($postId);
    }

    public function findByAuthor(int $authorId): ?CommentCollection
    {
        return $this->repository->findByAuthor($authorId);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
