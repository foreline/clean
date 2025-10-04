<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 * Filter class is designed to be extended for specific repository needs (add repository specific filter methods).
 * A filter is used to specify criteria for querying a repository.
 * It can hold multiple criteria, each represented as a key-value pair.
 * Criteria can be restricted, and conditions can be grouped with AND/OR logic.
 */
class Filter implements FilterInterface
{
    /** @var ServiceInterface|null Service instance */
    private ?ServiceInterface $service;
    
    public const EXPRESSION_INT_NOT = '!';
    public const EXPRESSION_STRING_LIKE = '%';
    public const EXPRESSION_STRING_NOT = '!';
    
    /** @var array  */
    private array $filter = [];
    /** @var array  */
    private array $restrictions = [];
    
    /**
     * @param ServiceInterface|null $service Service instance to which this filter belongs
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * Return filter criteria (with their values)
     * @return array<string,mixed>
     *
     * @refactor This method is too complex and needs to be simplified. Too many nested conditions.
     */
    public function get(): array
    {
        $result = [];
        
        foreach ( array_merge($this->filter, $this->restrictions) as $field => $value ) {
            if ( !isset($this->restrictions[$field]) ) {
                $result[$field] = $this->filter[$field];
            } elseif ( !isset($this->filter[$field]) ) {
                $result[$field] = $this->restrictions[$field];
            } else {
                
                if (
                    is_array($this->restrictions[$field])
                    && is_array($this->filter[$field])
                ) {
                    $result[$field] =
                        array_intersect($this->restrictions[$field], $this->filter[$field])
                            ?: $this->restrictions[$field];
                } elseif (
                    is_array($this->restrictions[$field])
                    && !is_array($this->filter[$field])
                ) {
                    
                    if ( in_array($this->filter[$field], $this->restrictions[$field], true) ) {
                        $result[$field] = $this->filter[$field];
                    } else {
                        $result[$field] = $this->restrictions[$field];
                    }
                    
                } elseif (
                    !is_array($this->restrictions[$field])
                    && is_array($this->filter[$field])
                ) {
                    
                    if ( in_array($this->restrictions[$field], $this->filter[$field], true) ) {
                        $result[$field] = $this->filter[$field];
                    } else {
                        $result[$field] = $this->restrictions[$field];
                    }
                    
                } else {
                    
                    if ( $this->filter[$field] === $this->restrictions[$field] ) {
                        $result[$field] = $this->filter[$field];
                    } else {
                        $result[$field] = $this->restrictions[$field];
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Set (overwrite all) filter criteria
     * @param array<string,mixed> $filter
     * @return self
     */
    public function set(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Add filter criteria. If criteria was already set, it will be overwritten with given value
     * @param string $field
     * @param $value
     * @param string $prefix
     * @param string $suffix
     * @return self
     * @noinspection PhpTooManyParametersInspection
     */
    public function add(string $field, $value, string $prefix = '', string $suffix = ''): self
    {
        $this->filter[$prefix . $field . $suffix] = $value;
        return $this;
    }
    
    /**
     * Add a condition to filter.
     * A condition is an array representing a set of criteria with a logical operator (AND/OR).
     * @param array $condition
     * @return $this
     */
    public function addCondition(array $condition): self
    {
        $this->filter['condition'][] = $condition;
        return $this;
    }
    
    /**
     * Restrict filter parameter by given value
     * If a parameter was already set, and contains the restrictions it will not be overwritten.
     * If a parameter was not set, it will be added with given value.
     * @param string $field
     * @param $value
     * @param string $prefix
     * @param string $suffix
     * @return $this
     * @noinspection PhpTooManyParametersInspection
     */
    public function restrict(string $field, $value, string $prefix = '', string $suffix = ''): self
    {
        $this->restrictions[$prefix . $field . $suffix] = $value;
        return $this;
    }
    
    /**
     * Unset filter criteria
     * @param string $field
     * @param string $prefix
     * @param string $suffix
     * @return $this
     */
    public function remove(string $field, string $prefix = '', string $suffix = ''): self
    {
        unset($this->filter[$prefix . $field . $suffix]);
        return $this;
    }
    
    /**
     * Add a criteria with inverse condition (NOT)
     * @param string $field
     * @param $value
     * @return $this
     */
    public function not(string $field, $value): self
    {
        if ( 'integer' === gettype($value) ) {
            $prefix = self::EXPRESSION_INT_NOT;
        } elseif ( 'string' === gettype($value) ) {
            $prefix = self::EXPRESSION_STRING_NOT;
        } else {
            $prefix = ''; // @fixme implement all types
        }
        
        $this->add($field, $value, $prefix);
        return $this;
    }
    
    /**
     * Reset (unset) filter criteria
     * @return self
     */
    public function reset(): static
    {
        $this->filter = [];
        return $this;
    }
    
    /**
     * Return service instance to which this filter belongs, or null if not set.
     * @return ?ServiceInterface
     */
    public function endFilter(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Starts a condition block where multiple criteria can be added with AND/OR logic.
     * Must be ended with endCondition() method for condition to be applied.
     * Multiple conditions can be added to a filter.
     * Conditions are combined with AND logic, while criteria inside a condition are combined with chosen ([OR]/AND) logic.
     * Return condition filter.
     * @return ConditionFilterInterface|null condition filter
     */
    public function byCondition(): ?ConditionFilterInterface
    {
        return new ConditionFilter($this);
    }
}