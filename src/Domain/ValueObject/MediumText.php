<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Средний текст (колонка mediumtext, бюджет 16777215 байт)
 *
 * Реальное ограничение даёт байтовый контроль (худший случай utf8mb4: 4 байта на символ).
 */
class MediumText extends AbstractLengthLimitedText
{
    /** Максимальная длина значения в символах (UTF-8) */
    protected const MAX_LENGTH = 16777215;

    /** Тип колонки БД */
    protected const COLUMN_TYPE = 'mediumtext';

    /** Байтовый бюджет колонки mediumtext */
    protected const MAX_BYTES = 16777215;
}
