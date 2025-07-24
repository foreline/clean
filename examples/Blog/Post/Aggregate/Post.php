<?php
declare(strict_types=1);

namespace App\Domain\Post\Aggregate;

use App\Domain\Post\Entity\PostEntity;
use App\Domain\Comment\Aggregate\CommentCollection;
use App\Domain\Taxonomy\Aggregate\CategoryCollection;
use App\Domain\Taxonomy\Aggregate\TagCollection;
use App\Domain\Comment\Aggregate\Comment;
use App\Domain\Taxonomy\Aggregate\Category;
use App\Domain\Taxonomy\Aggregate\Tag;
use Domain\Aggregate\AggregateInterface;

/**
 * Post Aggregate - Manages relationships and business logic
 */
class Post extends PostEntity implements AggregateInterface
{
    private CommentCollection $comments;
    private CategoryCollection $categories;
    private TagCollection $tags;

    public function __construct()
    {
        $this->comments = new CommentCollection();
        $this->categories = new CategoryCollection();
        $this->tags = new TagCollection();
    }

    public function getComments(): CommentCollection
    {
        return $this->comments;
    }

    public function setComments(CommentCollection $comments): void
    {
        $this->comments = $comments;
    }

    public function addComment(Comment $comment): void
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->addItem($comment);
        }
    }

    public function removeComment(Comment $comment): void
    {
        // Implementation would depend on collection's remove method
        // For now, we'll create a new collection without this comment
        $newComments = new CommentCollection();
        foreach ($this->comments as $existingComment) {
            if ($existingComment->getId() !== $comment->getId()) {
                $newComments->addItem($existingComment);
            }
        }
        $this->comments = $newComments;
    }

    public function getCategories(): CategoryCollection
    {
        return $this->categories;
    }

    public function setCategories(CategoryCollection $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(Category $category): void
    {
        if (!$this->categories->contains($category)) {
            $this->categories->addItem($category);
        }
    }

    public function removeCategory(Category $category): void
    {
        $newCategories = new CategoryCollection();
        foreach ($this->categories as $existingCategory) {
            if ($existingCategory->getId() !== $category->getId()) {
                $newCategories->addItem($existingCategory);
            }
        }
        $this->categories = $newCategories;
    }

    public function getTags(): TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->addItem($tag);
        }
    }

    public function removeTag(Tag $tag): void
    {
        $newTags = new TagCollection();
        foreach ($this->tags as $existingTag) {
            if ($existingTag->getId() !== $tag->getId()) {
                $newTags->addItem($existingTag);
            }
        }
        $this->tags = $newTags;
    }

    /**
     * Business rule: Check if post can accept comments
     */
    public function canAcceptComments(): bool
    {
        return !$this->isDraft();
    }

    /**
     * Business rule: Publish the post
     */
    public function publish(): void
    {
        $this->setDraft(false);
        if ($this->getPostDate() === null) {
            $this->setPostDate(new \DateTime());
        }
    }
}
