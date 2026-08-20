<?php
declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface that defines a string Value Object (VO) with a known length budget.
 *
 * Extends the plain string VO contract with static metadata describing
 * the maximum value length and the suggested database column type.
 * The metadata is static on purpose: code generators read it via reflection
 * without instantiating the value object.
 */
interface LengthAwareStringValueObjectInterface extends StringValueObjectInterface
{
    /**
     * Maximum value length in characters (mb_strlen, UTF-8).
     *
     * Intended consumers: UI form attributes, MCP/JSON-schema maxLength, validation.
     *
     * @return int
     */
    public static function maxLength(): int;

    /**
     * Suggested database column type for the value: varchar|text|mediumtext|longtext.
     *
     * Intended consumers: code generators building ORM maps and migrations.
     *
     * @return string
     */
    public static function columnType(): string;
}
