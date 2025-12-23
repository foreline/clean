<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\User\Infrastructure\Repository\UserFilter;
use Domain\User\Infrastructure\Repository\UserRepositoryInterface;

/**
 * User filter trait
 */
trait UserFilterTrait
{
    public UserFilter $filter;
    
    /**
     * @param array $filter
     * @return $this
     */
    public function filter(array $filter): static
    {
        $this->filter->set($filter);
        return $this;
    }
    
    /**
     * @param int|int[] $id
     * @param bool $inverse
     * @return $this
     */
    public function filterById(int|array $id, bool $inverse = false): static
    {
        if ( $inverse ) {
            $this->filter->not(UserRepositoryInterface::ID, $id);
        } else {
            $this->filter->add(UserRepositoryInterface::ID, $id);
        }
        return $this;
    }
    
    /**
     * @param bool|bool[] $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): static
    {
        $this->filter->add(UserRepositoryInterface::ACTIVE, $active);
        return $this;
    }
    
    /**
     * @param string|string[] $role
     * @return $this
     */
    public function filterByRole(string|array $role): static
    {
        $this->filter->filterByRole($role);
        return $this;
    }
    
    /**
     * @param string $email
     * @return $this
     */
    public function filterByEmail(string $email): static
    {
        $this->filter->add(UserRepositoryInterface::EMAIL, $email);
        return $this;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function filterByExtId(string $extId): static
    {
        $this->filter->add(UserRepositoryInterface::EXT_ID, $extId);
        return $this;
    }
    
    /**
     * Задает фильтр поиска
     * @param string $term
     * @return $this
     */
    public function search(string $term): static
    {
        $this->filter
            ->byCondition()
            ->searchByLastName($term)
            ->searchByName($term)
            ->searchByEmail($term)
            ->searchByPosition($term)
            ->searchByDepartment($term)
            ->searchByLogin($term)
            ->searchByPhone($term)
            ->endCondition()
        ;
        return $this;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function searchByExtId(string $extId): static
    {
        $this->filter->add(UserRepositoryInterface::EXT_ID, '%' . $extId . '%');
        return $this;
    }
}