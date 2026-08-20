<?php
declare(strict_types=1);

namespace Domain\ValueObject;

use InvalidArgumentException;

/**
 * Абстрактный текстовый Value Object с ограничением длины
 *
 * Базовый класс для текстовых VO с известным лимитом длины (ShortText, Text,
 * MediumText, LongText и их прикладные наследники). Лимиты задаются константами
 * и могут перегружаться в наследниках (late static binding):
 *
 * ```php
 * class ActionName extends ShortText
 * {
 *     protected const MAX_LENGTH = 100;
 * }
 * ```
 *
 * Валидация мультибайтовая: символьный лимит проверяется через mb_strlen(..., 'UTF-8'),
 * для text-семейства колонок дополнительно контролируется байтовый бюджет
 * (mb_strlen(..., '8bit')) — защита от худшего случая utf8mb4.
 *
 * Статические maxLength()/columnType() читаются кодогенератором через рефлексию
 * без инстанцирования объекта.
 */
abstract class AbstractLengthLimitedText implements LengthAwareStringValueObjectInterface
{
    /** Максимальная длина значения в символах (UTF-8) */
    protected const MAX_LENGTH = 255;

    /** Тип колонки БД: varchar|text|mediumtext|longtext */
    protected const COLUMN_TYPE = 'varchar';

    /**
     * Байтовый бюджет колонки БД; 0 — не проверять.
     * Для varchar достаточно символьного лимита (MySQL считает varchar(N) в символах),
     * для text-семейства лимит колонки задан в байтах.
     */
    protected const MAX_BYTES = 0;

    /** @var string Значение */
    private string $value;

    /**
     * @param string $value Значение
     * @throws InvalidArgumentException При превышении символьного или байтового лимита
     */
    public function __construct(string $value)
    {
        $length = mb_strlen($value, 'UTF-8');
        if ( $length > static::maxLength() ) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s: значение превышает максимальную длину %d символов (передано %d)',
                    static::class,
                    static::maxLength(),
                    $length
                )
            );
        }

        if ( static::maxBytes() > 0 ) {
            $bytes = mb_strlen($value, '8bit');
            if ( $bytes > static::maxBytes() ) {
                throw new InvalidArgumentException(
                    sprintf(
                        '%s: значение превышает байтовый лимит колонки %d байт (передано %d)',
                        static::class,
                        static::maxBytes(),
                        $bytes
                    )
                );
            }
        }

        $this->value = $value;
    }

    /**
     * Максимальная длина значения в символах (mb_strlen, UTF-8).
     * @return int
     */
    public static function maxLength(): int
    {
        return static::MAX_LENGTH;
    }

    /**
     * Тип колонки БД: varchar|text|mediumtext|longtext.
     * @return string
     */
    public static function columnType(): string
    {
        return static::COLUMN_TYPE;
    }

    /**
     * Байтовый бюджет колонки БД; 0 — без байтового контроля.
     * @return int
     */
    public static function maxBytes(): int
    {
        return static::MAX_BYTES;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Пусто ли значение.
     * @return bool
     */
    public function isEmpty(): bool
    {
        return '' === $this->value;
    }

    /**
     * Сравнение по классу и значению (PHP `==` для VO не использовать).
     * @param self|null $other
     * @return bool
     */
    public function equals(?self $other): bool
    {
        return null !== $other
            && static::class === $other::class
            && $this->value === $other->value;
    }
}
