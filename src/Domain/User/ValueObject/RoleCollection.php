<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

use Domain\ValueObject\CollectionInterface;
use Domain\ValueObject\CollectionTrait;
use Domain\ValueObject\ValueObjectInterface;

/**
 * User Role Collection
 */
class RoleCollection implements CollectionInterface
{
    use CollectionTrait;
    
    /** @var Role[] */
    private array $items;
    
    public function __construct()
    {
        $this->items = [];
        $this->position = 0;
    }
    
    /**
     * Return the current element
     * @return Role|null
     */
    public function current(): ?Role
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }
    
    /**
     * @return ?Role[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }
    
    /**
     * @param Role|ValueObjectInterface $role
     * @return self
     */
    public function addItem(Role|ValueObjectInterface $role): self
    {
        $this->items[] = $role;
        return $this;
    }
    
    /**
     * @param ?CollectionInterface $roles
     * @return $this
     */
    public function setItems(?CollectionInterface $roles): self
    {
        $this->items = [];
        
        foreach ( $roles as $item ) {
            $this->addItem($item);
        }
        return $this;
    }
    
    /**
     * @param CollectionInterface $roles
     * @return $this
     */
    public function addItems(CollectionInterface $roles): self
    {
        foreach ( $roles as $role ) {
            $this->addItem($role);
        }
        return $this;
    }
    
    /**
     * The __toString method allows a class to decide how it will react when it is converted to a string.
     */
    public function __toString(): string
    {
        return implode(', ', array_map(fn(Role $role): string => $role->getName(),$this->getCollection()));
    }
}