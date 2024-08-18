<?php
declare(strict_types=1);

namespace Domain\Event\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Aggregate\IteratorTrait;
use Domain\Event\Event;
use Iterator;

/**
 * Events Collection
 */
class EventCollection implements IteratorInterface
{
    /** @var ?Event[]  */
    private ?array $items = null;
    
    use IteratorTrait;

    /**
     * @return Event|null
     */
    public function current(): ?Event
    {
        return $this->valid() ? $this->items[$this->key()] : null;
    }

    /**
     * @return ?Event[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * @param AggregateInterface $item
     * @return $this
     */
    public function addItem(AggregateInterface $item): self
    {
        if ( null === $this->items ) {
            $this->items = [];
        }
        
        $this->items[] = $item;
        
        return $this;
    }

    /**
     * @param Iterator|null $items
     * @return $this
     */
    public function setItems(?Iterator $items): self
    {
        foreach ( $items as $item ) {
            $this->addItem($item);
        }
        
        return $this;
    }

    /**
     * @param array $fields
     * @return array|null
     */
    public function toArray(array $fields = []): ?array
    {
        return [
            'entityType'   => 'domain_event_collection',
            //'events'        => '',
        ];
    }
}