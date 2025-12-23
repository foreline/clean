<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\User\Infrastructure\Repository\UserRepositoryInterface;
use Domain\User\Infrastructure\Repository\UserSort;

/**
 * User sort trait
 */
trait UserSortTrait
{
    public UserSort $sort;
    
    /**
     * @param array<string,string> $sort
     * @return $this
     */
    public function sort(array $sort): static
    {
        $this->sort->set($sort);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortById(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::ID, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByLogin(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::LOGIN, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByEmail(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::EMAIL, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByName(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::NAME, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByLastName(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::LAST_NAME, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortBySecondName(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::SECOND_NAME, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByActive(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::ACTIVE, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByPosition(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::POSITION, $order);
        return $this;
    }
    
    /**
     * @param string $order
     * @return $this
     */
    public function sortByAvatar(string $order = 'asc'): static
    {
        $this->sort->add(UserRepositoryInterface::AVATAR, $order);
        return $this;
    }
    
    /**
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function sortBy(string $field, string $order = 'asc'): static
    {
        $this->sort->by($field, $order);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function sortByRand(): static
    {
        $this->sort->byRand();
        return $this;
    }
}