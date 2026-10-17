<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/** Неизменяемое логическое значение. В условиях используйте __toBoolean(). */
class BooleanValue implements BoolValueObjectInterface, \JsonSerializable
{
    /** @var bool Значение */
    private bool $value;

    /** Создать значение без неявного разбора строк. */
    public function __construct(bool $value = false)
    {
        $this->value = $value;
    }

    /** Получить значение для логического выражения. */
    public function __toBoolean(): bool
    {
        return $this->value;
    }

    /** Представить значение в JSON как boolean. */
    public function jsonSerialize(): bool
    {
        return $this->value;
    }

    /** Сравнить тип и значение. */
    public function equals(?self $other): bool
    {
        return null !== $other && static::class === $other::class && $this->value === $other->value;
    }
}
