<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface that defines a Value Object (VO) whose value type is an integer.
 */
interface IntValueObjectInterface
{
    /**
     * @param int $value
     */
    public function __construct(int $value);
    
    /**
     * @return int
     */
    public function __toInteger(): int;
}