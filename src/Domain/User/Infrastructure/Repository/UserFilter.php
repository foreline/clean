<?php
declare(strict_types=1);

namespace Domain\User\Infrastructure\Repository;

use Domain\Repository\Filter;
use Domain\Repository\FilterInterface;
use Domain\User\UseCase\GroupManager;

/**
 *
 */
class UserFilter extends Filter implements FilterInterface
{
    /**
     * @param int|int[] $id
     * @param bool $inverse
     * @return $this
     */
    public function filterById(int|array $id, bool $inverse = false): self
    {
        if ( $inverse ) {
            $this->not(UserRepositoryInterface::ID, $id);
        } else {
            $this->add(UserRepositoryInterface::ID, $id);
        }
        return $this;
    }
    
    /**
     * @param string|string[] $role
     * @return $this
     */
    public function filterByRole(string|array $role): self
    {
        $manager = new GroupManager();
        $manager->fields->set([GroupRepositoryInterface::ID]);
        
        $groupId = $manager
            ->filterByCode($role)
            ->find()?->current()?->getId();
        
        $this->add(UserRepositoryInterface::GROUPS, $groupId);
        return $this;
    }
    
    /**
     * @param bool|array $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): self
    {
        $this->add(UserRepositoryInterface::ACTIVE, $active);
        return $this;
    }
    
    /**
     * @return UserConditionFilter
     */
    public function byCondition(): UserConditionFilter
    {
        return new UserConditionFilter($this);
    }
}