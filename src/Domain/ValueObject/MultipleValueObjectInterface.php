<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface for ValueObject consisting of other ValueObjects and scalar types.
 * This interface must cast to string value for storing in database.
 */
interface MultipleValueObjectInterface extends StringValueObjectInterface
{
    
}