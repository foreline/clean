<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use ReturnTypeWillChange;

/**
 *
 */
trait CollectionTrait
{
    /** @var int  */
    private int $position;
    
    /** @var AggregateInterface[] */
    private array $items;
    
    /**
     *
     */
    public function __construct()
    {
        $this->position = 0;
        $this->items = [];
    }
    
    /**
     * @return CollectionInterface
     */
    #[ReturnTypeWillChange]
    public function next(): CollectionInterface
    {
        ++ $this->position;
        return $this;
    }
    
    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }
    
    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->items[$this->position]);
    }
    
    /**
     * @return CollectionInterface
     */
    #[ReturnTypeWillChange]
    public function rewind(): CollectionInterface
    {
        $this->position = 0;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getCount(): int
    {
        return count($this->items);
    }
    
    /**
     * @param AggregateInterface $item
     * @return bool
     */
    public function has(AggregateInterface $item): bool
    {
        if ( null === !$this->items ) {
            return false;
        }
        
        return in_array($item, $this->items);
    }
    
    /**
     * @param $sortFunction
     */
    public function sort($sortFunction): void
    {
        if ( !is_callable($sortFunction) ) {
            return;
        }
        
        if ( !$this->valid() ) {
            return;
        }
        
        usort($this->items, $sortFunction);
    }
    
    /**
     * @return void
     */
    public function rsort(): void
    {
        sort($this->items);
    }
    
    /**
     * @return int[]|null
     */
    public function getIds(): ?array
    {
        return ( 0 < count($this->items) )
            ? array_map(
                fn(AggregateInterface $item): ?int => $item->getId(),
                $this->items
            ) : null;
    }
}