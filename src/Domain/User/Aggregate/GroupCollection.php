<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Aggregate\IteratorTrait;
use Iterator;

class GroupCollection implements IteratorInterface
{
    use IteratorTrait;
    
    /** @var ?Group[]  */
    private ?array $items;

    /**
     * @return Group|null
     */
    public function current(): ?Group
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * @return ?Group[]
     */
    public function getCollection(): ?array
    {
        return $this->valid() ? $this->items : null;
    }

    /**
     * @param AggregateInterface|Group $group
     * @return $this
     */
    public function addItem(AggregateInterface|Group $group): self
    {
        $this->items[] = $group;
        return $this;
    }

    /**
     * @param Iterator|GroupCollection|null $groups
     * @return $this
     */
    public function setItems(null|Iterator|GroupCollection $groups): self
    {
        $this->items = null;
        
        foreach ( $groups as $group ) {
            $this->addItem($group);
        }
        return $this;
    }
}