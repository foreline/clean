<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Aggregate\IteratorTrait;
use Iterator;

/**
 * User collection
 */
class UserCollection implements UsersInterface, IteratorInterface
{
    use IteratorTrait;
    
    /** @var User[]  */
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
     * @return User[]|null
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }

    /**
     * @param Iterator|UsersInterface|null $users
     * @return UserCollection
     */
    public function setItems(null|Iterator|UsersInterface $users): self
    {
        $this->items = [];
        
        foreach ( $users as $user ) {
            $this->addItem($user);
        }
        return $this;
    }
    
    /**
     * Добавляет пользователя в коллекцию
     * @param UserInterface|AggregateInterface $user
     * @return $this
     */
    public function addItem(UserInterface|AggregateInterface $user): self
    {
        if ( !$this->contains($user) ) {
            $this->items[] = $user;
        }
        
        return $this;
    }

    /**
     * Добавляет пользователей в коллекцию
     * @param UserCollection $users
     * @return $this
     */
    public function addItems(UserCollection $users): self
    {
        foreach ( $users->getCollection() as $user ) {
            $this->addItem($user);
        }
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getEmails(): ?array
    {
        return $this->valid() ?
            array_map(
                static function($user) {
                    return $user->getEmail();
                },
                $this->items
            )
            : null;
    }

    /**
     * @return UserInterface|null
     */
    public function current(): ?UserInterface
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }

    /**
     * Returns elements count
     * @return int
     */
    public function getCount(): int
    {
        return $this->valid() ? count($this->items) : 0;
    }

    /**
     * @param array $fields
     * @return array|null
     */
    public function toArray(array $fields = []): ?array
    {
        return $this->valid() ? array_map(
            static function($user) use ($fields) {
                return $user->toArray($fields);
            },
            $this->items
        ) : null;
    }
    
    /**
     * @param UserInterface $user
     * @return bool
     */
    public function contains(UserInterface $user): bool
    {
        foreach ( $this->getCollection() as $collectionItem ) {
            if ( $collectionItem->getId() === $user->getId() ) {
                return true;
            }
        }
        return false;
    }
}