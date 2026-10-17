<?php
declare(strict_types=1);

namespace Tests\Domain\ValueObject;

use Domain\ValueObject\Active;
use Domain\ValueObject\ActiveValueObjectInterface;
use Domain\ValueObject\BooleanValue;
use Domain\ValueObject\BoolValueObjectInterface;
use Domain\ValueObject\SortOrder;
use Domain\ValueObject\SortValueObjectInterface;
use PHPUnit\Framework\TestCase;

/** @covers \Domain\ValueObject\BooleanValue
 * @covers \Domain\ValueObject\Active
 * @covers \Domain\ValueObject\SortOrder
 */
final class BooleanTypesTest extends TestCase
{
    /** False должен остаться false при преобразовании и сериализации. */
    public function testBooleanValues(): void
    {
        foreach ([false, true] as $value) {
            $vo = new BooleanValue($value);
            self::assertInstanceOf(BoolValueObjectInterface::class, $vo);
            self::assertSame($value, $vo->__toBoolean());
            self::assertSame($value, json_decode(json_encode($vo), true));
            self::assertTrue($vo->equals(new BooleanValue($value)));
            self::assertFalse($vo->equals(new BooleanValue(!$value)));
            self::assertFalse($vo->equals(new Active($value)));
        }
    }

    /** Маркеры не меняют скалярное значение и сохраняют прежние defaults. */
    public function testSemanticValues(): void
    {
        self::assertInstanceOf(ActiveValueObjectInterface::class, new Active());
        self::assertTrue((new Active())->__toBoolean());
        self::assertFalse((new Active(false))->__toBoolean());
        self::assertInstanceOf(SortValueObjectInterface::class, new SortOrder());
        self::assertSame(0, (new SortOrder())->__toInteger());
        foreach ([-5, 0, 42] as $value) {
            self::assertSame($value, (new SortOrder($value))->__toInteger());
            self::assertSame($value, json_decode(json_encode(new SortOrder($value)), true));
            self::assertTrue((new SortOrder($value))->equals(new SortOrder($value)));
            self::assertFalse((new SortOrder($value))->equals(new SortOrder($value + 1)));
        }
    }
}
