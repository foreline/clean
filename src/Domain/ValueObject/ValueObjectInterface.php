<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Value-Object Interface
 */
interface ValueObjectInterface
{
    /**
     * @return ValueObjectInterface[]
     */
    public static function getAll(): array;
}