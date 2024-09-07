<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface that defines a Value Object (VO) whose value type is a string.
 */
interface StringValueObjectInterface
{
    /**
     * @return string
     */
    public function __toString(): string;
}