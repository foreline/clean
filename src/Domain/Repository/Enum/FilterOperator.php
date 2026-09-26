<?php

declare(strict_types=1);

namespace Domain\Repository\Enum;

/**
 * Filter comparison operators for range filters
 *
 * Domain-agnostic operators that map to storage-specific implementations.
 * This enum is the Single Source of Truth for all filter operators.
 */
enum FilterOperator: string
{
    // Equality operators
    case EQUALS = 'eq';
    case NOT_EQUALS = 'neq';
    
    // Comparison operators (for integers, floats, dates)
    case GREATER_THAN = 'gt';
    case GREATER_OR_EQUAL = 'gte';
    case LESS_THAN = 'lt';
    case LESS_OR_EQUAL = 'lte';
    
    // Range operator
    case BETWEEN = 'between';
    
    // String operators
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'not_contains';
    case STARTS_WITH = 'starts';
    case ENDS_WITH = 'ends';
    case FILLED = 'filled';
    case UNFILLED = 'unfilled';
    
    // Collection operators
    case IN = 'in';
    case NOT_IN = 'nin';
    
    /**
     * Human-readable name in Russian
     */
    public function name(): string
    {
        return match ($this) {
            self::EQUALS => 'равно',
            self::NOT_EQUALS => 'не равно',
            self::GREATER_THAN => 'больше чем',
            self::GREATER_OR_EQUAL => 'больше или равно',
            self::LESS_THAN => 'меньше чем',
            self::LESS_OR_EQUAL => 'меньше или равно',
            self::BETWEEN => 'в диапазоне',
            self::CONTAINS => 'содержит',
            self::NOT_CONTAINS => 'не содержит',
            self::STARTS_WITH => 'начинается с',
            self::ENDS_WITH => 'заканчивается на',
            self::FILLED => 'заполнено',
            self::UNFILLED => 'не заполнено',
            self::IN => 'один из',
            self::NOT_IN => 'не один из',
        };
    }
    
    /**
     * Operators applicable to integer fields
     *
     * @return self[]
     */
    public static function forInteger(): array
    {
        return [
            self::EQUALS,
            self::NOT_EQUALS,
            self::GREATER_THAN,
            self::GREATER_OR_EQUAL,
            self::LESS_THAN,
            self::LESS_OR_EQUAL,
            self::BETWEEN,
        ];
    }
    
    /**
     * Operators applicable to float fields
     *
     * @return self[]
     */
    public static function forFloat(): array
    {
        return self::forInteger(); // Same as integer
    }
    
    /**
     * Operators applicable to date fields
     *
     * Note: Relative date values (today, this month, etc.) will be supported
     * in Phase 5 via RelativeDateAnchor enum. See Migration Strategy section.
     *
     * @return self[]
     */
    public static function forDate(): array
    {
        return [
            self::EQUALS,
            self::GREATER_THAN,
            self::GREATER_OR_EQUAL,
            self::LESS_THAN,
            self::LESS_OR_EQUAL,
            self::BETWEEN,
        ];
    }
    
    /**
     * Operators applicable to string fields
     *
     * @return self[]
     */
    public static function forString(): array
    {
        return [
            self::CONTAINS,
            self::NOT_CONTAINS,
            self::FILLED,
            self::UNFILLED,
        ];
    }
    
    /**
     * Check if this operator requires a range (min/max values)
     */
    public function isRange(): bool
    {
        return self::BETWEEN === $this;
    }
    
    /**
     * Check if this operator requires a value to compare against
     *
     * FILLED and UNFILLED check the field itself and carry no value.
     */
    public function requiresValue(): bool
    {
        return self::FILLED !== $this && self::UNFILLED !== $this;
    }
    
    /**
     * Check if this operator requires a single value
     */
    public function isSingleValue(): bool
    {
        return !$this->isRange() && $this->requiresValue() && self::IN !== $this && self::NOT_IN !== $this;
    }
}
