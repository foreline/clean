<?php
declare(strict_types=1);

namespace Domain\UseCase;

/**
 *
 */
class Filter
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

    /**
     * Adds filter by Active
     * @param bool|bool[] $active
     * @return self
     */
    public function byActive(bool|array $active = true): self
    {
        $this->add('active', $active);
        return $this;
    }

    /**
     * Adds filter by ID
     * @param int|int[] $id
     * @param bool $inverse
     * @return self
     */
    public function byId(int|array $id, bool $inverse = false): self
    {
        if ( $inverse ) {
            $this->not('id', $id);
        } else {
            $this->add('id', $id);
        }
        return $this;
    }

    /**
     * Фильтр по ID инициатора или ID инициаторов
     * @param int|int[] $id
     * @return self
     */
    public function byCreator(int|array $id): self
    {
        $this->add('created_by', $id);
        return $this;
    }

    /**
     * @param string $name
     * @param bool $strict
     * @return $this
     */
    public function byName(string $name, bool $strict = false): self
    {
        $prefix = $strict ? '' : self::EXPRESSION_STRING_LIKE;
        $suffix = $strict ? '' : self::EXPRESSION_STRING_LIKE;
        
        $this->add('name', $name, $prefix, $suffix);
        return $this;
    }
    
    /**
     * Добавляет условие к фильтру
     * @param array $condition
     * @param string $logic OR|AND
     * @return $this
     */
    public function addCondition(array $condition, string $logic = 'OR'): self
    {
        $condition['LOGIC'] = $logic;
        $this->add('condition', $condition);
        return $this;
    }
}