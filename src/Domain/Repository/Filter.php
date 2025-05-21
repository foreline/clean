<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 * Filter class
 */
class Filter implements FilterInterface
{
    private ?ServiceInterface $service;
    
    public const EXPRESSION_INT_NOT = '!';
    public const EXPRESSION_STRING_LIKE = '%';
    public const EXPRESSION_STRING_NOT = '!';
    
    /** @var array  */
    private array $filter = [];
    /** @var array  */
    private array $restrictions = [];
    
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * Returns filter parameters with their values
     * @return array<string,mixed>
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
                $result[$field] =
                    array_intersect($this->restrictions[$field], $this->filter[$field])
                    ?: $this->restrictions[$field];
            }
        }
        
        return $result;
    }
    
    /**
     * Sets (overwrites all) filter parameters
     * @param array<string,mixed> $filter
     * @return self
     */
    public function set(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }
    
    /**
     * Adds filter parameter. If parameter was already set, it will be overwritten with given value
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
     * Restricts filter parameter by given value.
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
     * Unsets filter parameter
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
     * Inverses filter
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
     * Resets filter parameters
     * @return self
     */
    public function reset(): static
    {
        $this->filter = [];
        return $this;
    }
    
    /**
     * Returns service
     * @return ?ServiceInterface
     */
    public function endFilter(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Returns condition filter
     * @return ConditionFilterInterface|null
     */
    public function byCondition(): ?ConditionFilterInterface
    {
        return new ConditionFilter($this);
    }
}