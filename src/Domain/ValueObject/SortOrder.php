<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/** Порядок записи. Меньшие значения располагаются раньше. */
class SortOrder implements SortValueObjectInterface, \JsonSerializable
{
    /** @var int Порядок */
    private int $value;

    /** Создать порядок, сохраняя поддержку отрицательных значений. */
    public function __construct(int $value = 0)
    {
        $this->value = $value;
    }

    /** Получить значение для хранения и сравнения. */
    public function __toInteger(): int
    {
        return $this->value;
    }

    /** Представить порядок в JSON как число. */
    public function jsonSerialize(): int
    {
        return $this->value;
    }

    /** Сравнить тип и значение. */
    public function equals(?self $other): bool
    {
        return null !== $other && static::class === $other::class && $this->value === $other->value;
    }
}
