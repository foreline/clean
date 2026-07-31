<?php
declare(strict_types=1);

namespace Tests\Presentation\Helpers;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Presentation\Helpers\DateIntervalAnnotator;

/**
 * Тесты аннотатора относительных интервалов дат
 *
 * @covers \Presentation\Helpers\DateIntervalAnnotator
 */
class DateIntervalAnnotatorTest extends TestCase
{
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new DateTimeImmutable('2026-07-23 15:00:00');
    }

    public function testAnnotatePastDate(): void
    {
        $date = new DateTimeImmutable('2025-07-01 15:01:53');

        $this->assertSame(
            '2025-07-01 (387 дней назад)',
            DateIntervalAnnotator::annotate($date, $this->now, 'Y-m-d')
        );
    }

    public function testAnnotateFutureDate(): void
    {
        $date = new DateTimeImmutable('2026-08-01 00:00:00');

        $this->assertSame(
            '2026-08-01 (через 9 дней)',
            DateIntervalAnnotator::annotate($date, $this->now, 'Y-m-d')
        );
    }

    public function testTodayYesterdayTomorrow(): void
    {
        $this->assertSame('(сегодня)', DateIntervalAnnotator::intervalLabel(0));
        $this->assertSame('(вчера)', DateIntervalAnnotator::intervalLabel(-1));
        $this->assertSame('(завтра)', DateIntervalAnnotator::intervalLabel(1));
    }

    public function testPluralization(): void
    {
        $this->assertSame('(через 2 дня)', DateIntervalAnnotator::intervalLabel(2));
        $this->assertSame('(через 5 дней)', DateIntervalAnnotator::intervalLabel(5));
        $this->assertSame('(21 день назад)', DateIntervalAnnotator::intervalLabel(-21));
        $this->assertSame('(22 дня назад)', DateIntervalAnnotator::intervalLabel(-22));
        $this->assertSame('(25 дней назад)', DateIntervalAnnotator::intervalLabel(-25));
        $this->assertSame('(111 дней назад)', DateIntervalAnnotator::intervalLabel(-111));
        $this->assertSame('(112 дней назад)', DateIntervalAnnotator::intervalLabel(-112));
    }

    public function testDaysBetweenUsesCalendarDays(): void
    {
        // Позднее время «сейчас» и ранняя дата в тот же день — всё ещё сегодня
        $date = new DateTimeImmutable('2026-07-23 00:00:01');
        $this->assertSame(0, DateIntervalAnnotator::daysBetween($date, $this->now));

        // Завтрашняя дата с временем раньше текущего — всё ещё «завтра»
        $date = new DateTimeImmutable('2026-07-24 01:00:00');
        $this->assertSame(1, DateIntervalAnnotator::daysBetween($date, $this->now));
    }

    public function testAnnotateStringIsoAndRuFormats(): void
    {
        $text = 'Зарегистрировано 2026-06-27 04:04:16, продление 01.07.2025, срок до 2026-08-01.';

        $result = DateIntervalAnnotator::annotateString($text, $this->now);

        $this->assertStringContainsString('2026-06-27 04:04:16 (26 дней назад)', $result);
        $this->assertStringContainsString('01.07.2025 (387 дней назад)', $result);
        $this->assertStringContainsString('2026-08-01 (через 9 дней)', $result);
    }

    public function testAnnotateStringSkipsAlreadyAnnotated(): void
    {
        $text = 'Дата 2025-07-01 (387 дней назад) и 2026-07-20 (3 дня назад).';

        $result = DateIntervalAnnotator::annotateString($text, $this->now);

        $this->assertSame($text, $result);
    }

    public function testAnnotateStringIgnoresVersionsAndInvalidDates(): void
    {
        $text = 'Veeam 11.0.1.1261 P20220302, дата 2026-13-45 не дата, id 20260723.';

        $result = DateIntervalAnnotator::annotateString($text, $this->now);

        $this->assertSame($text, $result);
    }

    public function testAnnotateStringIsIdempotent(): void
    {
        $text = 'Дата регистрации: 2026-07-05 15:11:15, изменено 23.07.2026 21:54:19, срок 2026.08.04 15:11:09.';

        $once = DateIntervalAnnotator::annotateString($text, $this->now);
        $twice = DateIntervalAnnotator::annotateString($once, $this->now);

        // Повторное применение не должно дробить секунды: «15:11 (X):15 (X)»
        $this->assertSame($once, $twice);
        $this->assertStringContainsString('2026-07-05 15:11:15 (18 дней назад)', $once);
    }

    public function testAnnotateStringCoversZabbixFormat(): void
    {
        $text = 'SSL certificate expires on 2026.08.04 15:11:09';

        $result = DateIntervalAnnotator::annotateString($text, $this->now);

        $this->assertSame('SSL certificate expires on 2026.08.04 15:11:09 (через 12 дней)', $result);
    }

    public function testStripAnnotationsRoundtrip(): void
    {
        $texts = [
            'Зарегистрировано 2026-06-27 04:04:16, продление 01.07.2025, срок до 2026-08-01.',
            'Дата регистрации: 2026-07-05 15:11:15, изменено 23.07.2026 21:54:19, срок 2026.08.04 15:11:09.',
            'SSL certificate expires on 2026.08.04 15:11:09',
            'Сегодня 2026-07-23, вчера 2026-07-22, завтра 2026-07-24.',
        ];

        foreach ( $texts as $text ) {
            $annotated = DateIntervalAnnotator::annotateString($text, $this->now);

            $this->assertSame(
                $text,
                DateIntervalAnnotator::stripAnnotations($annotated),
                'Roundtrip annotate→strip должен вернуть исходный текст'
            );
        }
    }

    public function testStripAnnotationsCoversAllLabelKinds(): void
    {
        $text = 'a 2026-07-23 (сегодня), b 2026-07-22 (вчера), c 2026-07-24 (завтра),'
            . ' d 2026-07-22 (1 день назад), e 2026-07-20 (3 дня назад), f 2026-07-11 (12 дней назад),'
            . ' g 2026-07-24 (через 1 день), h 2026-07-26 (через 3 дня), i 2026-08-04 (через 12 дней).';

        $expected = 'a 2026-07-23, b 2026-07-22, c 2026-07-24,'
            . ' d 2026-07-22, e 2026-07-20, f 2026-07-11,'
            . ' g 2026-07-24, h 2026-07-26, i 2026-08-04.';

        $this->assertSame($expected, DateIntervalAnnotator::stripAnnotations($text));
    }

    public function testStripAnnotationsLeavesPlainTextUntouched(): void
    {
        $texts = [
            // Скобки не после даты — легитимный текст
            'Интервал (5 дней назад) указан вручную, дата где-то рядом.',
            'Описание (сегодня) без даты.',
            // Невалидные даты и версии не аннотируются — и не стрипаются
            'Veeam 11.0.1.1261 P20220302, дата 2026-13-45 не дата.',
            // Обычные скобки после даты, но не метка интервала
            'Дата 2026-07-23 (утро) и 2026-07-24 (по UTC).',
        ];

        foreach ( $texts as $text ) {
            $this->assertSame($text, DateIntervalAnnotator::stripAnnotations($text));
        }
    }

    public function testStripAnnotationsHandlesStaleCopiedLabels(): void
    {
        // Случай из практики: агент скопировал дату с устаревшей меткой из старого
        // контекста в текст действия — при записи метка снимается, дата остаётся.
        $text = 'Срок истекает через 13 дней (2026-07-23 (5 дней назад)).';

        $this->assertSame(
            'Срок истекает через 13 дней (2026-07-23).',
            DateIntervalAnnotator::stripAnnotations($text)
        );
    }
}
