<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Though Value-Objects are immutable and ID agnostic,
 * in some cases we need to store values somewhere.
 */
interface PersistedValueObjectInterface extends ValueObjectInterface
{
    /**
     * @return float|int|string
     */
    public function getId(): float|int|string;
}