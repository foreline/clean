<?php
declare(strict_types=1);

namespace App\Domain\Comment\Aggregate;

use Domain\Aggregate\CollectionInterface;

/**
 * Comment Collection
 */
class CommentCollection implements CollectionInterface
{
    use \Domain\Aggregate\IteratorTrait;

    /** @var ?Comment[] */
    private ?array $items;

    /**
     * @return ?Comment
     */
    public function current(): ?Comment
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return ?Comment
     */
    public function previous(): ?Comment
    {
        return $this->items[$this->position - 1] ?? null;
    }

    /**
     * @return ?Comment[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * Adds element to Collection
     * @param Comment|\Domain\Aggregate\AggregateInterface $item
     * @return self
     */
    public function addItem(\Domain\Aggregate\AggregateInterface $item): self
    {
        if ($item instanceof Comment && !$this->contains($item)) {
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
     * @param CommentCollection|null $commentCollection
     * @return $this
     */
    public function setItems(\Iterator|null $commentCollection): self
    {
        $this->items = null;
        if ($commentCollection) {
            foreach ($commentCollection as $comment) {
                $this->addItem($comment);
            }
        }
        return $this;
    }

    /**
     * @param Comment $item
     * @return bool
     */
    public function contains(Comment $item): bool
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
