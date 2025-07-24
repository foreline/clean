<?php
declare(strict_types=1);

namespace App\Domain\Post\Aggregate;

use Domain\Aggregate\CollectionInterface;

/**
 * Post Collection
 */
class PostCollection implements CollectionInterface
{
    use \Domain\Aggregate\IteratorTrait;

    /** @var ?Post[] */
    private ?array $items;

    /**
     * @return ?Post
     */
    public function current(): ?Post
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return ?Post
     */
    public function previous(): ?Post
    {
        return $this->items[$this->position - 1] ?? null;
    }

    /**
     * @return ?Post[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * Adds element to Collection
     * @param Post|\Domain\Aggregate\AggregateInterface $item
     * @return self
     */
    public function addItem(\Domain\Aggregate\AggregateInterface $item): self
    {
        if ($item instanceof Post && !$this->contains($item)) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Adds elements to Collection
     * @param \Domain\Aggregate\CollectionInterface $items
     * @return $this
     */
    public function addItems(\Domain\Aggregate\CollectionInterface $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * @param \Iterator|null $postCollection
     * @return $this
     */
    public function setItems(?\Iterator $postCollection): self
    {
        $this->items = null;
        if ($postCollection) {
            foreach ($postCollection as $post) {
                $this->addItem($post);
            }
        }
        return $this;
    }

    /**
     * @param Post $item
     * @return bool
     */
    public function contains(Post $item): bool
    {
        if (!$item->getId()) {
            return false;
        }
        foreach ($this->getCollection() ?? [] as $collectionItem) {
            if ($collectionItem->getId() === $item->getId()) {
                return true;
            }
        }
        return false;
    }
}
