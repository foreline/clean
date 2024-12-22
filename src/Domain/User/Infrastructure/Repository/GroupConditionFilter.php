<?php
declare(strict_types=1);

namespace Domain\User\Infrastructure\Repository;

use Domain\Repository\ConditionFilter;
use Domain\Repository\ConditionFilterInterface;
use Domain\Repository\FilterInterface;

/**
 *
 */
class GroupConditionFilter extends ConditionFilter implements ConditionFilterInterface
{
    private GroupFilter $groupFilter;
    private array $condition;
    
    /**
     * @param GroupFilter|FilterInterface $groupFilter
     */
    public function __construct(GroupFilter|FilterInterface $groupFilter)
    {
        $this->condition['LOGIC'] = 'OR';
        $this->groupFilter = $groupFilter;
    }
    
    /**
     * @param string $name
     * @return $this
     */
    public function searchByName(string $name): self
    {
        $this->condition[GroupRepositoryInterface::NAME] = '%' . $name . '%';
        return $this;
    }
    
    /**
     * @return FilterInterface
     */
    public function endCondition(): FilterInterface
    {
        $this->groupFilter->add('condition', $this->condition);
        return $this->groupFilter;
    }
}