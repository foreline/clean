<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Aggregate;

use Domain\Aggregate\CollectionInterface;

/**
 * Tag Collection
 */
class TagCollection implements CollectionInterface
{
    use \Domain\Aggregate\IteratorTrait;

    /** @var ?Tag[] */
    private ?array $items;

    /**
     * @return ?Tag
     */
    public function current(): ?Tag
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return ?Tag
     */
    public function previous(): ?Tag
    {
        return $this->items[$this->position - 1] ?? null;
    }

    /**
     * @return ?Tag[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * Adds element to Collection
     * @param Tag|\Domain\Aggregate\AggregateInterface $item
     * @return self
     */
    public function addItem(\Domain\Aggregate\AggregateInterface $item): self
    {
        if ($item instanceof Tag && !$this->contains($item)) {
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
     * @param \Iterator|null $tagCollection
     * @return $this
     */
    public function setItems(?\Iterator $tagCollection): self
    {
        $this->items = null;
        if ($tagCollection) {
            foreach ($tagCollection as $tag) {
                $this->addItem($tag);
            }
        }
        return $this;
    }

    /**
     * @param Tag $item
     * @return bool
     */
    public function contains(Tag $item): bool
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
