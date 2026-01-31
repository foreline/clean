<?php

declare(strict_types=1);

namespace Domain\Repository\Enum;

/**
 * Keys used in range filter data structures
 *
 * Single Source of Truth for all range filter array keys.
 * Used in serialization, deserialization, and UI form field names.
 */
enum RangeFilterKey: string
{
    /** Operator key */
    case OPERATOR = 'op';
    
    /** Single value key (for non-range operators) */
    case VALUE = 'val';
    
    /** Minimum value key (for BETWEEN operator) */
    case MIN = 'min';
    
    /** Maximum value key (for BETWEEN operator) */
    case MAX = 'max';
    
    /** Relative date anchor key (for relative date filters) */
    case RELATIVE = 'relative';
    
    /** Relative minimum date anchor key (for relative date ranges) */
    case RELATIVE_MIN = 'relativeMin';
    
    /** Relative maximum date anchor key (for relative date ranges) */
    case RELATIVE_MAX = 'relativeMax';
}
