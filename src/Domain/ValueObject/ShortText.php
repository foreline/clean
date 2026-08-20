<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Короткий текст (до 255 символов, колонка varchar(255))
 *
 * Базовый VO для name-подобных свойств сущностей. Наследники могут
 * ужесточать лимит перегрузкой константы MAX_LENGTH.
 */
class ShortText extends AbstractLengthLimitedText
{
    /** Максимальная длина значения в символах (UTF-8) */
    protected const MAX_LENGTH = 255;

    /** Тип колонки БД */
    protected const COLUMN_TYPE = 'varchar';
}
