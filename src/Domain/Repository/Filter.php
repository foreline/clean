<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
class Filter implements FilterInterface
{
    public const EXPRESSION_INT_NOT = '!';
    public const EXPRESSION_STRING_LIKE = '%';
    
    /** @var array  */
    private array $filter = [];

    /**
     * Returns filter parameters
     * @return array<string,mixed>
     */
    public function get(): array
    {
        return $this->filter;
    }

    /**
     * Sets filter parameters
     * @param array<string,mixed> $filter
     * @return self
     */
    public function set(array $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Adds filter parameter
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
     * @param string $field
     * @param $value
     * @return $this
     */
    public function not(string $field, $value): self
    {
        if ( 'integer' === gettype($value) ) {
            $prefix = self::EXPRESSION_INT_NOT;
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
    public function reset(): self
    {
        $this->filter = [];
        return $this;
    }
}