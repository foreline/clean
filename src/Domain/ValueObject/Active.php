<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/** Активность записи; новая запись по умолчанию активна. */
class Active extends BooleanValue implements ActiveValueObjectInterface
{
    /** Создать признак активности. */
    public function __construct(bool $value = true)
    {
        parent::__construct($value);
    }
}
