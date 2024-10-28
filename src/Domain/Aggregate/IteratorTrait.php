<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use Domain\ValueObject\ValueObjectInterface;
use ReturnTypeWillChange;

/**
 *
 */
trait IteratorTrait
{
    /** @var int  */
    private int $position;
    
    /** @var ?AggregateInterface[] */
    private ?array $items;
    
    /**
     *
     */
    public function __construct()
    {
        $this->position = 0;
        $this->items = null;
    }
    
    /**
     * @return IteratorInterface
     */
    #[ReturnTypeWillChange]
    public function next(): IteratorInterface
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
     * @return IteratorInterface
     */
    #[ReturnTypeWillChange]
    public function rewind(): IteratorInterface
    {
        $this->position = 0;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getCount(): int
    {
        return ( null !== $this->items ? count($this->items) : 0 );
    }
    
    /**
     * @param bool $withUrl
     * @param string $delimiter
     * @return string
     */
    public function toString(bool $withUrl = true, string $delimiter = ', '): string
    {
        if ( !$this->valid() ) {
            return '';
        }
        
        return implode(
            $delimiter,
            array_map(
                fn (AggregateInterface $item): string => ($withUrl ? '<a href="' . $item->getSlug() . '">' : '') . $item->getName() . ($withUrl ? '</a>' : '')
                ,$this->items
            )
        );
    }
    
    /**
     * @param array $fields
     * @return array|null
     */
    public function toArray(array $fields = []): ?array
    {
        return $this->valid()
            ? array_map(
                fn($item): ?array => $item->toArray($fields),
                $this->items
            ) : null;
    }
    
    /**
     * @return int[]|null
     */
    public function getIds(): ?array
    {
        return $this->valid()
            ? array_map(
                fn(AggregateInterface $item): ?int => $item->getId(),
                $this->items
            ) : null;
    }
    
    /**
     * @param AggregateInterface|ValueObjectInterface $item
     * @return bool
     */
    public function has(AggregateInterface|ValueObjectInterface $item): bool
    {
        if ( null === !$this->items ) {
            return false;
        }
        
        return in_array($item, $this->items);
    }
    
    /**
     * @return void
     */
    public function rsort(): void
    {
        sort($this->items);
    }
}