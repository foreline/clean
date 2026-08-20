<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Текст (колонка text, бюджет 65535 байт)
 *
 * Символьный лимит совпадает с байтовым и никогда не срабатывает раньше —
 * реальное ограничение даёт байтовый контроль (худший случай utf8mb4: 4 байта на символ).
 */
class Text extends AbstractLengthLimitedText
{
    /** Максимальная длина значения в символах (UTF-8) */
    protected const MAX_LENGTH = 65535;

    /** Тип колонки БД */
    protected const COLUMN_TYPE = 'text';

    /** Байтовый бюджет колонки text */
    protected const MAX_BYTES = 65535;
}
