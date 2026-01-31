<?php

declare(strict_types=1);

namespace Domain\Repository\ValueObject;

use Domain\Repository\Enum\FilterOperator;
use Domain\Repository\Enum\RangeFilterKey;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing a range filter with operator and value(s)
 *
 * Immutable. Supports serialization for filter persistence.
 *
 * Examples:
 * - Single value: RangeFilterValue::greaterThan(5)
 * - Range: RangeFilterValue::between(1, 10)
 */
final class RangeFilterValue implements JsonSerializable
{
    /**
     * @param FilterOperator $operator The comparison operator
     * @param int|float|null $value Single value (for non-range operators)
     * @param int|float|null $min Minimum value (for BETWEEN operator)
     * @param int|float|null $max Maximum value (for BETWEEN operator)
     */
    private function __construct(
        private readonly FilterOperator $operator,
        private readonly int|float|null $value = null,
        private readonly int|float|null $min = null,
        private readonly int|float|null $max = null,
    ) {
        $this->validate();
    }
    
    /**
     * Validates the value object state
     *
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if ($this->operator->isRange()) {
            if (null === $this->min || null === $this->max) {
                throw new InvalidArgumentException(
                    "BETWEEN operator requires both min and max values"
                );
            }
            if ($this->min > $this->max) {
                throw new InvalidArgumentException(
                    "min value ({$this->min}) cannot be greater than max value ({$this->max})"
                );
            }
        } elseif ($this->operator->isSingleValue()) {
            if (null === $this->value) {
                throw new InvalidArgumentException(
                    "Operator {$this->operator->value} requires a value"
                );
            }
        }
    }
    
    // Factory methods (named constructors)
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function equals(int|float $value): self
    {
        return new self(FilterOperator::EQUALS, $value);
    }
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function notEquals(int|float $value): self
    {
        return new self(FilterOperator::NOT_EQUALS, $value);
    }
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function greaterThan(int|float $value): self
    {
        return new self(FilterOperator::GREATER_THAN, $value);
    }
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function greaterOrEqual(int|float $value): self
    {
        return new self(FilterOperator::GREATER_OR_EQUAL, $value);
    }
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function lessThan(int|float $value): self
    {
        return new self(FilterOperator::LESS_THAN, $value);
    }
    
    /**
     * @param int|float $value
     * @return static
     */
    public static function lessOrEqual(int|float $value): self
    {
        return new self(FilterOperator::LESS_OR_EQUAL, $value);
    }
    
    /**
     * @param int|float $min
     * @param int|float $max
     * @return static
     */
    public static function between(int|float $min, int|float $max): self
    {
        return new self(FilterOperator::BETWEEN, null, $min, $max);
    }
    
    /**
     * Create from array (for deserialization)
     *
     * @param array{op: string, val?: int|float, min?: int|float, max?: int|float} $data
     */
    public static function fromArray(array $data): self
    {
        $operator = FilterOperator::from($data[RangeFilterKey::OPERATOR->value]);
        
        if ($operator->isRange()) {
            return new self(
                $operator,
                null,
                $data[RangeFilterKey::MIN->value] ?? null,
                $data[RangeFilterKey::MAX->value] ?? null
            );
        }
        
        return new self($operator, $data[RangeFilterKey::VALUE->value] ?? null);
    }
    
    /**
     * @return FilterOperator
     */
    public function getOperator(): FilterOperator
    {
        return $this->operator;
    }
    
    /**
     * @return int|float|null
     */
    public function getValue(): int|float|null
    {
        return $this->value;
    }
    
    /**
     * @return int|float|null
     */
    public function getMin(): int|float|null
    {
        return $this->min;
    }
    
    /**
     * @return int|float|null
     */
    public function getMax(): int|float|null
    {
        return $this->max;
    }
    
    /**
     * Check if this represents a range filter (vs simple equality)
     */
    public function isRangeFilter(): bool
    {
        return FilterOperator::EQUALS !== $this->operator;
    }
    
    // Serialization
    
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        if ($this->operator->isRange()) {
            return [
                RangeFilterKey::OPERATOR->value => $this->operator->value,
                RangeFilterKey::MIN->value => $this->min,
                RangeFilterKey::MAX->value => $this->max,
            ];
        }
        
        return [
            RangeFilterKey::OPERATOR->value => $this->operator->value,
            RangeFilterKey::VALUE->value => $this->value,
        ];
    }
    
    /**
     * Convert to simple value if this is an equality filter
     *
     * Returns the simple value for EQUALS operator, or the full array for range operators.
     * This allows backward-compatible storage.
     *
     * @return int|float|array
     */
    public function toStorageValue(): int|float|array
    {
        // For simple equality, store just the value (backward compatible)
        if (FilterOperator::EQUALS === $this->operator) {
            return $this->value;
        }
        
        // For range operators, store the full structure
        return $this->jsonSerialize();
    }
}
