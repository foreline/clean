<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
class ConditionFilter implements ConditionFilterInterface
{
    private FilterInterface $filter;
    private array $condition;
    
    /**
     * @param FilterInterface $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->condition['LOGIC'] = 'OR';
        $this->filter = $filter;
    }
    
    /**
     * @return FilterInterface
     */
    public function endCondition(): FilterInterface
    {
        $this->filter->add('condition', $this->condition);
        return $this->filter;
    }
}