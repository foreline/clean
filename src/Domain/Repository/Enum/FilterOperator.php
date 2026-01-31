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
    case STARTS_WITH = 'starts';
    case ENDS_WITH = 'ends';
    
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
            self::STARTS_WITH => 'начинается с',
            self::ENDS_WITH => 'заканчивается на',
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
     * Check if this operator requires a range (min/max values)
     */
    public function isRange(): bool
    {
        return self::BETWEEN === $this;
    }
    
    /**
     * Check if this operator requires a single value
     */
    public function isSingleValue(): bool
    {
        return !$this->isRange() && self::IN !== $this && self::NOT_IN !== $this;
    }
}
