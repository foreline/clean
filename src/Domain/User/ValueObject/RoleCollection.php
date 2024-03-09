<?php
    declare(strict_types=1);
    
    namespace Domain\User\ValueObject;

    use Domain\Aggregate\AggregateInterface;
    use Domain\Aggregate\IteratorInterface;
    use Domain\Aggregate\IteratorTrait;
    use Iterator;

    /**
     * Коллекция ролей пользователя
     */
    class RoleCollection implements IteratorInterface
    {
        use IteratorTrait;
        
        /** @var ?Role[] */
        private ?array $items;
        
        public function __construct()
        {
            $this->items = null;
            $this->position = 0;
        }
        
        /**
         * Return the current element
         * @link https://php.net/manual/en/iterator.current.php
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
         * @param Role|AggregateInterface $role
         * @return self
         */
        public function addItem(Role|AggregateInterface $role): self
        {
            if ( null === $this->items ) {
                $this->items = [];
            }
            $this->items[] = $role;
            return $this;
        }
    
        /**
         * @param RoleCollection|Iterator|null $roles
         * @return $this
         */
        public function setItems(RoleCollection|null|Iterator $roles): self
        {
            $this->items = null;
            
            foreach ( $roles as $item ) {
                $this->addItem($item);
            }
            return $this;
        }
    
        /**
         * The __toString method allows a class to decide how it will react when it is converted to a string.
         *
         * @return string
         * @link https://php.net/manual/en/language.oop5.magic.php#language.oop5.magic.tostring
         */
        public function __toString(): string
        {
            return implode(', ', array_map(fn(Role $role): string => $role->getName(),$this->getCollection()));
        }
    }