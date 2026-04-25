<?php
declare(strict_types=1);

namespace Tests\Presentation\Form;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Presentation\Form\InputCoercer;

/**
 * @covers \Presentation\Form\InputCoercer
 */
class InputCoercerTest extends TestCase
{
    /**
     * @dataProvider stringProvider
     */
    public function testString(mixed $input, ?string $expected): void
    {
        self::assertSame($expected, InputCoercer::string($input));
    }

    /**
     * @return array<string, array{mixed, string|null}>
     */
    public static function stringProvider(): array
    {
        return [
            'null'          => [null, null],
            'empty'         => ['', null],
            'plain'         => ['hello', 'hello'],
            'ascii trim'   => ['  hello  ', 'hello'],
            'nbsp trim'    => ["\u{00A0}hello\u{00A0}", 'hello'],
            'nnbsp trim'   => ["\u{202F}hello\u{202F}", 'hello'],
            'inner spaces' => ['a b c', 'a b c'],
            'int cast'     => [42, '42'],
            'float cast'   => [3.14, '3.14'],
        ];
    }

    public function testStringRejectsArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InputCoercer::string(['a']);
    }

    /**
     * @dataProvider intProvider
     */
    public function testInt(mixed $input, ?int $expected): void
    {
        self::assertSame($expected, InputCoercer::int($input));
    }

    /**
     * @return array<string, array{mixed, int|null}>
     */
    public static function intProvider(): array
    {
        return [
            'null'             => [null, null],
            'empty'            => ['', null],
            'plain'            => ['42', 42],
            'with spaces'     => ['  42  ', 42],
            'phone'            => ['+7 (495) 123-45-67', 74951234567],
            'leading minus'   => ['-1234', -1234],
            'leading plus'    => ['+1234', 1234],
            'thousands dot'   => ['1.234.567', 1234567],
            'thousands comma' => ['1,234,567', 1234567],
            'float-shaped'    => ['3.14', 3],
            'float trunc neg' => ['-3.99', -3],
            'native int'      => [42, 42],
            'native float'    => [3.14, 3],
            'decorated'       => ['#42', 42],
        ];
    }

    /**
     * @dataProvider intRejectProvider
     */
    public function testIntRejectsNonNumeric(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        InputCoercer::int($input);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function intRejectProvider(): array
    {
        return [
            'letters only' => ['abc'],
            'sign only'    => ['+'],
            'array'        => [[1, 2]],
            'true bool'    => [true],
        ];
    }

    /**
     * @dataProvider floatProvider
     */
    public function testFloat(mixed $input, ?float $expected): void
    {
        self::assertSame($expected, InputCoercer::float($input));
    }

    /**
     * @return array<string, array{mixed, float|null}>
     */
    public static function floatProvider(): array
    {
        return [
            'null'                  => [null, null],
            'empty'                 => ['', null],
            'plain'                 => ['3.14', 3.14],
            'space thousands dot'   => ['1 999.99', 1999.99],
            'space thousands comma' => ['1 999,99', 1999.99],
            'nbsp thousands'        => ["1\u{00A0}999.99", 1999.99],
            'nnbsp thousands'       => ["1\u{202F}999,99", 1999.99],
            'us grouping'           => ['1,999.99', 1999.99],
            'eu grouping'           => ['1.999,99', 1999.99],
            'single dot decimal'    => ['1.234', 1.234],
            'single dot thousands'  => ['1.2345', 12345.0],
            'single comma decimal'  => ['1,234', 1.234],
            'single comma thousands'=> ['1,2345', 12345.0],
            'currency prefix'       => ['$ 1 234.56', 1234.56],
            'currency suffix'       => ['1 234.56 руб', 1234.56],
            'leading minus'         => ['-1 234,56', -1234.56],
            'leading plus'          => ['+1 234,56', 1234.56],
            'native int'            => [42, 42.0],
            'native float'          => [3.14, 3.14],
        ];
    }

    /**
     * @dataProvider floatRejectProvider
     */
    public function testFloatRejectsNonNumeric(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        InputCoercer::float($input);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function floatRejectProvider(): array
    {
        return [
            'letters only' => ['abc'],
            'sign only'    => ['-'],
            'sep only'     => [','],
            'array'        => [[1.5]],
            'true bool'    => [true],
        ];
    }

    /**
     * @dataProvider boolProvider
     */
    public function testBool(mixed $input, bool $expected): void
    {
        self::assertSame($expected, InputCoercer::bool($input));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function boolProvider(): array
    {
        return [
            'null'         => [null, false],
            'empty string' => ['', false],
            'on'           => ['on', true],
            'off'          => ['off', false],
            'yes'          => ['yes', true],
            'no'           => ['no', false],
            'y'            => ['y', true],
            'n'            => ['n', false],
            'true str'    => ['true', true],
            'false str'   => ['false', false],
            'checked'      => ['checked', true],
            '1'            => ['1', true],
            '0'            => ['0', false],
            'uppercase ON'=> ['ON', true],
            'whitespace'   => ["  yes\u{00A0}", true],
            'native true'  => [true, true],
            'native false' => [false, false],
            'native int 1' => [1, true],
            'native int 0' => [0, false],
        ];
    }

    /**
     * @dataProvider boolRejectProvider
     */
    public function testBoolRejectsUnknown(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        InputCoercer::bool($input);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function boolRejectProvider(): array
    {
        return [
            'maybe' => ['maybe'],
            'two'   => ['2'],
            'array' => [['on']],
        ];
    }
}
