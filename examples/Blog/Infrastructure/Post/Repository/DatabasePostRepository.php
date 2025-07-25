<?php
declare(strict_types=1);

namespace App\Infrastructure\Post\Repository;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Aggregate\PostCollection;
use App\Domain\Post\Repository\PostRepositoryInterface;

/**
 * Database implementation of Post Repository
 */
class DatabasePostRepository implements PostRepositoryInterface
{
    private array $posts = []; // Simulating database storage
    private int $nextId = 1;

    public function persist(Post $post): Post
    {
        if (!$post->getId()) {
            $post->setId($this->nextId++);
        }
        
        $this->posts[$post->getId()] = $post;
        
        return $post;
    }

    public function findById(int $id): ?Post
    {
        return $this->posts[$id] ?? null;
    }

    public function find(): ?PostCollection
    {
        $collection = new PostCollection();
        foreach ($this->posts as $post) {
            $collection->addItem($post);
        }
        
        return $collection;
    }

    public function findByAuthor(int $authorId): ?PostCollection
    {
        $collection = new PostCollection();
        foreach ($this->posts as $post) {
            if ($post->getAuthorId() === $authorId) {
                $collection->addItem($post);
            }
        }
        
        return $collection;
    }

    public function findByCategory(int $categoryId): ?PostCollection
    {
        $collection = new PostCollection();
        foreach ($this->posts as $post) {
            foreach ($post->getCategories() as $category) {
                if ($category->getId() === $categoryId) {
                    $collection->addItem($post);
                    break;
                }
            }
        }
        
        return $collection;
    }

    public function findByTag(int $tagId): ?PostCollection
    {
        $collection = new PostCollection();
        foreach ($this->posts as $post) {
            foreach ($post->getTags() as $tag) {
                if ($tag->getId() === $tagId) {
                    $collection->addItem($post);
                    break;
                }
            }
        }
        
        return $collection;
    }

    public function delete(int $id): bool
    {
        if (isset($this->posts[$id])) {
            unset($this->posts[$id]);
            return true;
        }
        
        return false;
    }
}
