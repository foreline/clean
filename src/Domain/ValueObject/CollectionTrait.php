<?php
declare(strict_types=1);

namespace Domain\ValueObject;

use ReturnTypeWillChange;

/**
 * Collection trait for ValueObjects
 */
trait CollectionTrait
{
    /** @var int  */
    private int $position;
    
    /** @var ValueObjectInterface[] */
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
     * @param ValueObjectInterface $item
     * @return bool
     */
    public function has(ValueObjectInterface $item): bool
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
}