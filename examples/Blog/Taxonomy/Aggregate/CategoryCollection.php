<?php
declare(strict_types=1);

namespace App\Domain\Taxonomy\Aggregate;

use Domain\Aggregate\CollectionInterface;

/**
 * Category Collection
 */
class CategoryCollection implements CollectionInterface
{
    use \Domain\Aggregate\IteratorTrait;

    /** @var ?Category[] */
    private ?array $items;

    /**
     * @return ?Category
     */
    public function current(): ?Category
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return ?Category
     */
    public function previous(): ?Category
    {
        return $this->items[$this->position - 1] ?? null;
    }

    /**
     * @return ?Category[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * Adds element to Collection
     * @param Category|\Domain\Aggregate\AggregateInterface $item
     * @return self
     */
    public function addItem(\Domain\Aggregate\AggregateInterface $item): self
    {
        if ($item instanceof Category && !$this->contains($item)) {
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
     * @param \Iterator|null $categoryCollection
     * @return $this
     */
    public function setItems(?\Iterator $categoryCollection): self
    {
        $this->items = null;
        if ($categoryCollection) {
            foreach ($categoryCollection as $category) {
                $this->addItem($category);
            }
        }
        return $this;
    }

    /**
     * @param Category $item
     * @return bool
     */
    public function contains(Category $item): bool
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
