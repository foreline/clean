<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Длинный текст (колонка longtext, бюджет 4294967295 байт)
 *
 * Реальное ограничение даёт байтовый контроль (худший случай utf8mb4: 4 байта на символ).
 */
class LongText extends AbstractLengthLimitedText
{
    /** Максимальная длина значения в символах (UTF-8) */
    protected const MAX_LENGTH = 4294967295;

    /** Тип колонки БД */
    protected const COLUMN_TYPE = 'longtext';

    /** Байтовый бюджет колонки longtext */
    protected const MAX_BYTES = 4294967295;
}
