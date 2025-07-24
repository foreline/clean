<?php
declare(strict_types=1);

namespace App\Domain\Comment\Repository;

use App\Domain\Comment\Aggregate\Comment;
use App\Domain\Comment\Aggregate\CommentCollection;

/**
 * Comment Repository Interface
 */
interface CommentRepositoryInterface
{
    public const ID = 'id';
    public const AUTHOR = 'author';
    public const COMMENT = 'comment';
    public const DATE = 'date';
    public const POST_ID = 'post_id';

    /**
     * @param Comment $comment
     * @return Comment
     */
    public function persist(Comment $comment): Comment;

    /**
     * @param int $id
     * @return ?Comment
     */
    public function findById(int $id): ?Comment;

    /**
     * @return ?CommentCollection
     */
    public function find(): ?CommentCollection;

    /**
     * @param int $postId
     * @return ?CommentCollection
     */
    public function findByPost(int $postId): ?CommentCollection;

    /**
     * @param int $authorId
     * @return ?CommentCollection
     */
    public function findByAuthor(int $authorId): ?CommentCollection;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
