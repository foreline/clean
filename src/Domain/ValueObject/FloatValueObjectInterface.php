<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface that defines a Value Object (VO) whose value type is a float.
 */
interface FloatValueObjectInterface
{
    /**
     * @param float $value
     */
    public function __construct(float $value);
    
    /**
     * @return float
     */
    public function __toFloat(): float;
}