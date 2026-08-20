<?php
declare(strict_types=1);

namespace Tests\ValueObject\Text;

use Domain\ValueObject\AbstractLengthLimitedText;
use Domain\ValueObject\LongText;
use Domain\ValueObject\MediumText;
use Domain\ValueObject\ShortText;
use Domain\ValueObject\Text;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Тесты семейства текстовых VO с ограничением длины.
 *
 * @covers \Domain\ValueObject\AbstractLengthLimitedText
 * @covers \Domain\ValueObject\ShortText
 * @covers \Domain\ValueObject\Text
 * @covers \Domain\ValueObject\MediumText
 * @covers \Domain\ValueObject\LongText
 */
class LengthLimitedTextTest extends TestCase
{
    /**
     * @return void
     */
    public function testMetadata(): void
    {
        static::assertSame(255, ShortText::maxLength());
        static::assertSame('varchar', ShortText::columnType());
        static::assertSame(0, ShortText::maxBytes());

        static::assertSame('text', Text::columnType());
        static::assertSame(65535, Text::maxBytes());

        static::assertSame('mediumtext', MediumText::columnType());
        static::assertSame(16777215, MediumText::maxBytes());

        static::assertSame('longtext', LongText::columnType());
        static::assertSame(4294967295, LongText::maxBytes());
    }

    /**
     * @return void
     */
    public function testBoundaryValueAccepted(): void
    {
        // Arrange
        $value = str_repeat('а', 255);

        // Act
        $text = new ShortText($value);

        // Assert
        static::assertSame($value, $text->getValue());
        static::assertSame($value, (string) $text);
        static::assertFalse($text->isEmpty());
    }

    /**
     * @return void
     */
    public function testTooLongValueRejected(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        new ShortText(str_repeat('a', 256));
    }

    /**
     * @return void
     */
    public function testMultibyteCountsCharactersNotBytes(): void
    {
        // 255 двухбайтовых кириллических символа = 510 байт, но 255 символов — допустимо
        // Act
        $text = new ShortText(str_repeat('я', 255));

        // Assert
        static::assertSame(255, mb_strlen((string) $text, 'UTF-8'));
    }

    /**
     * @return void
     */
    public function testByteBudgetRejectedForTextFamily(): void
    {
        // 16384 четырёхбайтовых эмодзи = 65536 байт > бюджета text (65535)
        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        new Text(str_repeat("\u{1F600}", 16384));
    }

    /**
     * @return void
     */
    public function testByteBudgetAcceptedForTextFamily(): void
    {
        // 16383 эмодзи = 65532 байта — в пределах бюджета text
        // Act
        $text = new Text(str_repeat("\u{1F600}", 16383));

        // Assert
        static::assertSame(16383, mb_strlen((string) $text, 'UTF-8'));
    }

    /**
     * @return void
     */
    public function testLimitOverrideInChildClass(): void
    {
        // Arrange
        $short = new class('ok') extends ShortText {
            protected const MAX_LENGTH = 2;
        };

        // Assert
        static::assertSame(2, $short::maxLength());
        static::assertSame('ok', (string) $short);

        $this->expectException(InvalidArgumentException::class);

        // Act
        new class('toolong') extends ShortText {
            protected const MAX_LENGTH = 2;
        };
    }

    /**
     * @return void
     */
    public function testEquals(): void
    {
        // Arrange
        $a = new ShortText('name');
        $b = new ShortText('name');
        $c = new ShortText('other');

        // Assert
        static::assertTrue($a->equals($b));
        static::assertFalse($a->equals($c));
        static::assertFalse($a->equals(null));
    }

    /**
     * @return void
     */
    public function testIsEmpty(): void
    {
        static::assertTrue((new ShortText(''))->isEmpty());
    }
}
