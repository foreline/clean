<?php

declare(strict_types=1);

namespace Domain\ValueObject\Aggregation;

use Domain\Aggregate\CollectionInterface;

/**
 * Результат группировки.
 *
 * @template TKey of GroupKey
 */
final class GroupResult
{
    /**
     * @var GroupKey Ключ группировки
     * @phpstan-var TKey
     */
    private GroupKey $key;
    
    /** @var int Количество */
    private int $count;
    
    /** @var ?CollectionInterface Элементы группы */
    private ?CollectionInterface $items;
    
    /**
     * @param TKey                     $key   Ключ группировки
     * @param int                      $count Количество
     * @param CollectionInterface|null $items Элементы группы
     */
    public function __construct(GroupKey $key, int $count, ?CollectionInterface $items = null)
    {
        $this->key   = $key;
        $this->count = $count;
        $this->items = $items;
    }
    
    /**
     * @return TKey
     */
    public function getKey(): GroupKey
    {
        return $this->key;
    }
    
    public function getCount(): int
    {
        return $this->count;
    }
    
    public function getItems(): ?CollectionInterface
    {
        return $this->items;
    }
    
    public function hasItems(): bool
    {
        return null !== $this->items;
    }
}
