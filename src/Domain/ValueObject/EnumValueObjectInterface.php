<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Value objects which can be represented as enums.
 */
interface EnumValueObjectInterface extends ValueObjectInterface
{
    /**
     * Returns all possible values.
     *
     * @return array
     */
    public static function map(): array;
}