<?php
declare(strict_types=1);

namespace App\Domain\Post\Repository;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Aggregate\PostCollection;

/**
 * Post Repository Interface
 */
interface PostRepositoryInterface
{
    public const ID = 'id';
    public const TITLE = 'title';
    public const CONTENT = 'content';
    public const AUTHOR = 'author';
    public const POST_DATE = 'post_date';
    public const IS_DRAFT = 'is_draft';
    public const CATEGORIES = 'categories';
    public const TAGS = 'tags';
    public const COMMENTS = 'comments';

    /**
     * @param Post $post
     * @return Post
     */
    public function persist(Post $post): Post;

    /**
     * @param int $id
     * @return ?Post
     */
    public function findById(int $id): ?Post;

    /**
     * @return ?PostCollection
     */
    public function find(): ?PostCollection;

    /**
     * @param int $authorId
     * @return ?PostCollection
     */
    public function findByAuthor(int $authorId): ?PostCollection;

    /**
     * @param int $categoryId
     * @return ?PostCollection
     */
    public function findByCategory(int $categoryId): ?PostCollection;

    /**
     * @param int $tagId
     * @return ?PostCollection
     */
    public function findByTag(int $tagId): ?PostCollection;

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
