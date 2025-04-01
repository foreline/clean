<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Aggregate\IteratorTrait;
use Iterator;

/**
 * User's group collection
 */
class GroupCollection implements IteratorInterface
{
    use IteratorTrait;
    
    /** @var ?Group[]  */
    private ?array $items;
    
    /**
     *
     */
    public function __construct()
    {
        $this->position = 0;
        $this->items = [];
    }

    /**
     * @return Group|null
     */
    public function current(): ?Group
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return Group[]
     */
    public function getCollection(): array
    {
        return $this->valid() ? $this->items : [];
    }

    /**
     * @param AggregateInterface|Group $group
     * @return $this
     */
    public function addItem(AggregateInterface|Group $group): self
    {
        if ( !$this->contains($group) ) {
            $this->items[] = $group;
        }
        
        return $this;
    }

    /**
     * @param Iterator|GroupCollection|null $groups
     * @return $this
     */
    public function setItems(null|Iterator|GroupCollection $groups): self
    {
        $this->items = [];
        
        foreach ( $groups as $group ) {
            $this->addItem($group);
        }
        return $this;
    }
    
    /**
     * @param GroupInterface $group
     * @return bool
     */
    public function contains(GroupInterface $group): bool
    {
        foreach ( $this->getCollection() as $collectionItem ) {
            if ( $collectionItem->getId() === $group->getId() ) {
                return true;
            }
        }
        return false;
    }
}