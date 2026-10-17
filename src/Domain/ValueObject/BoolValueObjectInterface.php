<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/** Логическое значение с явным преобразованием в bool. */
interface BoolValueObjectInterface
{
    /** Создать логическое значение. */
    public function __construct(bool $value);

    /** Вернуть скалярное значение для условий и границ системы. */
    public function __toBoolean(): bool;
}
