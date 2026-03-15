<?php

declare(strict_types=1);

namespace Domain\ValueObject\Aggregation;

use Iterator;

/**
 * Коллекция результатов группировки.
 *
 * @template TKey of GroupKey
 * @implements Iterator<int, GroupResult<TKey>>
 */
final class GroupResultCollection implements Iterator
{
    /** @var int Позиция */
    private int $position = 0;
    
    /** @var GroupResult<TKey>[] Группы */
    private array $items = [];
    
    /**
     * @return GroupResult<TKey>|null
     */
    public function current(): ?GroupResult
    {
        return $this->items[$this->position] ?? null;
    }
    
    public function key(): int
    {
        return $this->position;
    }
    
    public function next(): void
    {
        $this->position++;
    }
    
    public function rewind(): void
    {
        $this->position = 0;
    }
    
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
    
    /**
     * @param GroupResult $item
     * @return GroupResultCollection
     */
    public function addItem(GroupResult $item): self
    {
        $this->items[] = $item;
        return $this;
    }
    
    public function count(): int
    {
        return count($this->items);
    }
    
    /**
     * Общее количество по всем группам.
     */
    public function getTotalCount(): int
    {
        $total = 0;
        
        foreach ( $this->items as $item ) {
            $total += $item->getCount();
        }
        
        return $total;
    }
    
    /**
     * @return GroupResult<TKey>[]
     */
    public function getCollection(): array
    {
        return $this->items;
    }
}
