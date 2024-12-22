<?php
declare(strict_types=1);

namespace Domain\User\Infrastructure\Repository;

use Domain\Repository\Filter;

/**
 *
 */
class GroupFilter extends Filter
{
    /**
     * @param int|int[] $id
     * @param bool $inverse
     * @return $this
     */
    public function filterById(int|array $id, bool $inverse = false): self
    {
        if ( $inverse ) {
            $this->not(GroupRepositoryInterface::ID, $id);
        } else {
            $this->add(GroupRepositoryInterface::ID, $id);
        }
        return $this;
    }
    
    /**
     * @param bool|array $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): self
    {
        $this->add(GroupRepositoryInterface::ACTIVE, $active);
        return $this;
    }
    
    /**
     * @return GroupConditionFilter
     */
    public function byCondition(): GroupConditionFilter
    {
        return new GroupConditionFilter($this);
    }
}