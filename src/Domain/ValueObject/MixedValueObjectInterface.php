<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface for ValueObject consisting of other ValueObjects and scalar types.
 * This interface must cast to string value, implement __toString() method.
 */
interface MixedValueObjectInterface extends ValueObjectInterface
{
    
}