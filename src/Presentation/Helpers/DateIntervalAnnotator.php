<?php
declare(strict_types=1);

namespace Presentation\Helpers;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Аннотатор относительных интервалов дат.
 *
 * Дополняет даты предвычисленным интервалом относительно «сейчас»:
 * «2025-07-01 (387 дней назад)», «2026-08-01 (через 9 дней)», «(сегодня)».
 *
 * Предназначен в первую очередь для текстов, потребляемых ИИ-агентами
 * (MCP-инструменты, контекстные документы, промпты): слабые модели
 * систематически ошибаются в календарной арифметике, поэтому вычисление
 * переносится из модели в код. Полезно и для читаемости людьми.
 */
class DateIntervalAnnotator
{
    /**
     * Возвращает дату с аннотацией интервала: «2025-07-01 (387 дней назад)».
     *
     * @param DateTimeInterface $date Аннотируемая дата
     * @param DateTimeImmutable|null $now Точка отсчёта (по умолчанию — текущее время)
     * @param string $format Формат вывода самой даты
     * @return string
     */
    public static function annotate(
        DateTimeInterface $date,
        ?DateTimeImmutable $now = null,
        string $format = 'Y-m-d H:i:s',
    ): string {
        return $date->format($format) . ' ' . self::intervalLabel(self::daysBetween($date, $now));
    }

    /**
     * Число полных календарных дней между «сейчас» и датой.
     * Положительное — дата в будущем, отрицательное — в прошлом.
     *
     * @param DateTimeInterface $date
     * @param DateTimeImmutable|null $now
     * @return int
     */
    public static function daysBetween(DateTimeInterface $date, ?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();
        $date = DateTimeImmutable::createFromInterface($date);

        $diff = $now->setTime(0, 0)->diff($date->setTime(0, 0));

        return 1 === $diff->invert ? -(int) $diff->days : (int) $diff->days;
    }

    /**
     * Текстовая метка интервала: «(387 дней назад)», «(через 9 дней)», «(сегодня)».
     *
     * @param int $days Результат daysBetween()
     * @return string
     */
    public static function intervalLabel(int $days): string
    {
        return match (true) {
            0 === $days   => '(сегодня)',
            1 === $days   => '(завтра)',
            -1 === $days  => '(вчера)',
            $days > 1     => '(через ' . $days . ' ' . self::pluralDays($days) . ')',
            default       => '(' . abs($days) . ' ' . self::pluralDays($days) . ' назад)',
        };
    }

    /**
     * Аннотирует все даты внутри готового текста (Markdown-документа).
     *
     * Распознаются форматы «Y-m-d[ H:i[:s]]» и «d.m.Y[ H:i[:s]]».
     * Даты, уже имеющие аннотацию («(N дней назад)», «(сегодня)» и т.п.),
     * пропускаются — повторная обработка не дублирует метки.
     *
     * Невероятные даты (месяц > 12, день > 31) и похожие на даты строки
     * (версии вида «11.0.1.1261») не аннотируются.
     *
     * @param string $text Исходный текст
     * @param DateTimeImmutable|null $now Точка отсчёта
     * @return string
     */
    public static function annotateString(string $text, ?DateTimeImmutable $now = null): string
    {
        $now ??= new DateTimeImmutable();

        // Уже аннотированная дата: за ней следует метка интервала
        $notAnnotated = '(?!\s*\((?:\d+\s+дн|сегодня|вчера|завтра|через\s+\d+\s+дн))';

        // ISO: 2026-07-23 или 2026-07-23 21:32[:25]
        $text = (string) preg_replace_callback(
            '/\b\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?' . $notAnnotated . '/',
            static fn(array $m): string => self::annotateMatch($m[0], $now),
            $text
        );

        // RU: 23.07.2026 или 23.07.2026 21:32[:25]
        $text = (string) preg_replace_callback(
            '/\b\d{2}\.\d{2}\.\d{4}(?: \d{2}:\d{2}(?::\d{2})?)?' . $notAnnotated . '/',
            static fn(array $m): string => self::annotateMatch($m[0], $now),
            $text
        );

        return $text;
    }

    /**
     * Аннотирует одну распознанную подстроку с датой; невалидные даты возвращает как есть.
     *
     * @param string $match
     * @param DateTimeImmutable $now
     * @return string
     */
    private static function annotateMatch(string $match, DateTimeImmutable $now): string
    {
        $date = self::parseLenient($match);

        if ( null === $date ) {
            return $match;
        }

        return $match . ' ' . self::intervalLabel(self::daysBetween($date, $now));
    }

    /**
     * Строгий разбор даты в поддерживаемых форматах; null при невалидной дате.
     *
     * @param string $value
     * @return DateTimeImmutable|null
     */
    private static function parseLenient(string $value): ?DateTimeImmutable
    {
        $formats = [
            'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d',
            'd.m.Y H:i:s', 'd.m.Y H:i', 'd.m.Y',
        ];

        foreach ( $formats as $format ) {
            $date = DateTimeImmutable::createFromFormat($format, $value);

            if ( $date instanceof DateTimeImmutable && $date->format($format) === $value ) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Русская плюрализация: день / дня / дней.
     *
     * @param int $n
     * @return string
     */
    private static function pluralDays(int $n): string
    {
        $n = abs($n);
        $mod100 = $n % 100;

        if ( $mod100 >= 11 && $mod100 <= 14 ) {
            return 'дней';
        }

        return match ( $n % 10 ) {
            1       => 'день',
            2, 3, 4 => 'дня',
            default => 'дней',
        };
    }
}
