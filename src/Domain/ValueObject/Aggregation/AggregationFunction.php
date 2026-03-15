<?php

declare(strict_types=1);

namespace Domain\ValueObject\Aggregation;

/**
 * Функция агрегации SQL.
 */
enum AggregationFunction: string
{
    case COUNT = 'COUNT(*)';
    case SUM   = 'SUM(%s)';
    case AVG   = 'AVG(%s)';
    case MIN   = 'MIN(%s)';
    case MAX   = 'MAX(%s)';
    
    /**
     * Наименование.
     */
    public function name(): string
    {
        return match ($this) {
            self::COUNT => 'Количество',
            self::SUM   => 'Сумма',
            self::AVG   => 'Среднее',
            self::MIN   => 'Минимум',
            self::MAX   => 'Максимум',
        };
    }
}
