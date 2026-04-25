<?php
declare(strict_types=1);

namespace Presentation\Form;

use InvalidArgumentException;

/**
 * Tolerant HTML form value coercer.
 *
 * Bridges the L1 → L2 boundary: turns the loose, string-only values that
 * an HTML form encoding produces (`application/x-www-form-urlencoded`,
 * `multipart/form-data`) into well-formed PHP scalars that downstream
 * Value Object constructors and entity setters can rely on.
 *
 * Design rules (intentionally less strict than PHP native casts):
 *
 * - Null-safe. `null` and the empty string map to `null` for `string`,
 *   `int` and `float`; `bool` follows HTML checkbox semantics
 *   (`null`/`""`/missing key → `false`).
 * - Tolerates locale separators, NBSP (U+00A0), NNBSP (U+202F),
 *   currency symbols and noise characters around digits.
 * - For numeric methods, throws {@see InvalidArgumentException} when the
 *   input contains no digit at all (e.g. `"abc"`). This is the only
 *   point where the coercer is *stricter* than PHP native casts —
 *   silent `0` from typo'd payloads tends to hide real bugs.
 * - `bool` recognises the HTML form vocabulary (`on`/`off`/`yes`/`no`/
 *   `true`/`false`/`1`/`0`/`y`/`n`/`checked`) and throws on anything
 *   else.
 *
 * The class is the *only* place in the framework that knows about HTML
 * form conventions. Value Objects, entities, services and repositories
 * stay strictly typed and are unaware of this layer.
 */
final class InputCoercer
{
    /**
     * Whitespace characters trimmed from string-shaped inputs, including
     * the regular ASCII set plus NBSP (U+00A0) and NNBSP (U+202F) often
     * emitted by client-side number formatters and locale-aware UIs.
     */
    private const TRIM_CHARS = " \t\n\r\0\x0B\u{00A0}\u{202F}";

    /**
     * Truthy tokens recognised by {@see self::bool()}.
     *
     * @var array<int, string>
     */
    private const BOOL_TRUE = ['1', 'on', 'yes', 'y', 'true', 'checked'];

    /**
     * Falsy tokens recognised by {@see self::bool()}.
     *
     * @var array<int, string>
     */
    private const BOOL_FALSE = ['0', 'off', 'no', 'n', 'false', ''];

    /**
     * Coerce raw form input to a string.
     *
     * Trims ASCII whitespace plus NBSP/NNBSP. Empty results map to
     * `null` so optional fields stay optional.
     *
     * @param mixed $value Raw value from `$_POST`/`$_GET`/`$data[KEY]`.
     * @return string|null Trimmed string, or `null` for `null`/empty input.
     */
    public static function string(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            throw new InvalidArgumentException(
                'InputCoercer::string() does not accept array/non-stringable object input.'
            );
        }

        $string = trim((string) $value, self::TRIM_CHARS);

        return '' === $string ? null : $string;
    }

    /**
     * Coerce raw form input to an int.
     *
     * Strips every non-digit character except a leading sign, so phone
     * numbers (`"+7 (495) 123-45-67"` → `74951234567`) and decorated
     * counters (`"#42"` → `42`) survive. A float-shaped string
     * (`"3.14"`) is truncated to its integer part — matching PHP's
     * native `(int)` cast for the digit portion.
     *
     * @param mixed $value Raw value from a form payload.
     * @return int|null `null` for `null`/empty input.
     * @throws InvalidArgumentException When the input has no digit.
     */
    public static function int(mixed $value): ?int
    {
        $normalised = self::normaliseNumeric($value);
        if (null === $normalised) {
            return null;
        }

        // Drop fractional part if present — int() always truncates.
        $dotPosition = strpos($normalised, '.');
        if (false !== $dotPosition) {
            $normalised = substr($normalised, 0, $dotPosition);
        }

        if ('' === $normalised || '-' === $normalised || '+' === $normalised) {
            throw new InvalidArgumentException(
                sprintf('InputCoercer::int() expects a digit; got %s.', var_export($value, true))
            );
        }

        return (int) $normalised;
    }

    /**
     * Coerce raw form input to a float.
     *
     * Tolerant of mixed thousands and decimal separators:
     *
     * - `"1 999.99"`  → `1999.99`
     * - `"1 999,99"`  → `1999.99`
     * - `"1,999.99"`  → `1999.99` (US grouping)
     * - `"1.999,99"`  → `1999.99` (German/Russian grouping)
     * - `"1.234"`     → `1.234`   (single dot, ≤ 3 trailing digits → decimal)
     * - `"1.2345"`    → `12345.0` (single dot, > 3 trailing digits → thousands)
     * - `"1,234"`     → `1.234`   (single comma, ≤ 3 trailing digits → decimal)
     * - `"1,2345"`    → `12345.0` (single comma, > 3 trailing digits → thousands)
     *
     * The "last separator wins" rule disambiguates mixed-separator input
     * deterministically. For locale-strict parsing, project authors can
     * call {@see self::floatLocale()} (requires `ext-intl`).
     *
     * @param mixed $value Raw value from a form payload.
     * @return float|null `null` for `null`/empty input.
     * @throws InvalidArgumentException When the input has no digit.
     */
    public static function float(mixed $value): ?float
    {
        $normalised = self::normaliseNumeric($value);
        if (null === $normalised) {
            return null;
        }

        if ('' === $normalised || '-' === $normalised || '+' === $normalised || '.' === $normalised) {
            throw new InvalidArgumentException(
                sprintf('InputCoercer::float() expects a digit; got %s.', var_export($value, true))
            );
        }

        return (float) $normalised;
    }

    /**
     * Coerce raw form input to a bool.
     *
     * Unlike PHP's `(bool)`, this method is strict on the *vocabulary*:
     * unknown tokens (e.g. `"maybe"`) raise an exception rather than
     * silently becoming `true`. The accepted vocabulary mirrors what
     * HTML forms actually emit:
     *
     * - Truthy: `1`, `on`, `yes`, `y`, `true`, `checked`
     * - Falsy:  `0`, `off`, `no`, `n`, `false`, empty string, `null`
     *
     * @param mixed $value Raw value from a form payload, or `null` when
     *                     the form key was absent (unchecked checkbox).
     * @return bool
     * @throws InvalidArgumentException On unknown vocabulary.
     */
    public static function bool(mixed $value): bool
    {
        if (null === $value || false === $value) {
            return false;
        }

        if (true === $value) {
            return true;
        }

        if (is_int($value) || is_float($value)) {
            return 0.0 !== (float) $value;
        }

        if (is_array($value) || is_object($value)) {
            throw new InvalidArgumentException(
                'InputCoercer::bool() does not accept array/object input.'
            );
        }

        $token = strtolower(trim((string) $value, self::TRIM_CHARS));

        if (in_array($token, self::BOOL_TRUE, true)) {
            return true;
        }

        if (in_array($token, self::BOOL_FALSE, true)) {
            return false;
        }

        throw new InvalidArgumentException(
            sprintf('InputCoercer::bool() does not recognise %s.', var_export($value, true))
        );
    }

    /**
     * Locale-aware float parser (opt-in, requires `ext-intl`).
     *
     * Use when the application needs strict locale semantics — for
     * example, refusing `"1.234"` as `1.234` under `de_DE` because the
     * dot is unambiguously a thousands separator there.
     *
     * @param mixed       $value  Raw value.
     * @param string|null $locale BCP-47 locale; defaults to the system locale.
     * @return float|null `null` for `null`/empty input.
     * @throws InvalidArgumentException When parsing fails or `ext-intl`
     *                                  is not loaded.
     */
    public static function floatLocale(mixed $value, ?string $locale = null): ?float
    {
        if (null === $value) {
            return null;
        }

        $string = trim((string) $value, self::TRIM_CHARS);
        if ('' === $string) {
            return null;
        }

        if (!class_exists(\NumberFormatter::class)) {
            throw new InvalidArgumentException(
                'InputCoercer::floatLocale() requires ext-intl to be loaded.'
            );
        }

        $formatter = new \NumberFormatter($locale ?? \Locale::getDefault(), \NumberFormatter::DECIMAL);
        $position  = 0;
        $result    = $formatter->parse($string, \NumberFormatter::TYPE_DOUBLE, $position);

        if (false === $result || $position < strlen($string)) {
            throw new InvalidArgumentException(
                sprintf('InputCoercer::floatLocale() failed to parse %s.', var_export($value, true))
            );
        }

        return (float) $result;
    }

    /**
     * Strip presentation noise around a numeric input and return a
     * normalised string suitable for `(int)`/`(float)` casts. Returns
     * `null` for `null`/empty input.
     *
     * @param mixed $value Raw value.
     * @return string|null Normalised numeric string, or `null`.
     * @throws InvalidArgumentException On non-stringable / digitless input.
     */
    private static function normaliseNumeric(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value) || is_array($value) || (is_object($value) && !method_exists($value, '__toString'))) {
            throw new InvalidArgumentException(
                'InputCoercer numeric methods do not accept bool/array/non-stringable object input.'
            );
        }

        $string = trim((string) $value, self::TRIM_CHARS);
        if ('' === $string) {
            return null;
        }

        // Capture an optional leading sign.
        $sign = '';
        if (str_starts_with($string, '+') || str_starts_with($string, '-')) {
            $sign   = $string[0];
            $string = substr($string, 1);
        }

        // Drop everything that is not a digit or one of the two separators.
        $string = preg_replace('/[^0-9.,]/', '', $string) ?? '';
        if ('' === $string) {
            throw new InvalidArgumentException('InputCoercer numeric input has no digits.');
        }

        $hasDot   = str_contains($string, '.');
        $hasComma = str_contains($string, ',');

        if ($hasDot && $hasComma) {
            // Last-seen separator wins as the decimal mark; the other is
            // a thousands grouping and is removed entirely.
            $decimal = strrpos($string, '.') > strrpos($string, ',') ? '.' : ',';
            $other   = '.' === $decimal ? ',' : '.';
            $string  = str_replace($other, '', $string);
            if (',' === $decimal) {
                $string = str_replace(',', '.', $string);
            }
        } elseif ($hasComma) {
            $string = self::resolveSingleSeparator($string, ',');
        } elseif ($hasDot) {
            $string = self::resolveSingleSeparator($string, '.');
        }

        // Sanity: at this point we should have at most one '.' and only digits.
        if (1 < substr_count($string, '.')) {
            // Multiple dots survived (e.g. "1.2.3") — treat as digits-only.
            $string = str_replace('.', '', $string);
        }

        return $sign . $string;
    }

    /**
     * Apply the single-separator heuristic.
     *
     * - One occurrence with 1–3 trailing digits → decimal mark.
     * - Otherwise → thousands grouping (removed).
     *
     * Behaviour is symmetric for `.` and `,` so callers don't need to
     * know which separator they're handling.
     *
     * @param string $string Numeric string with exactly one separator type.
     * @param string $sep    Either `.` or `,`.
     * @return string Normalised string with at most one `.` decimal mark.
     */
    private static function resolveSingleSeparator(string $string, string $sep): string
    {
        $count = substr_count($string, $sep);
        if (1 === $count) {
            $tail        = strrchr($string, $sep);
            $digitsAfter = false === $tail ? 0 : strlen($tail) - 1;
            if ($digitsAfter >= 1 && $digitsAfter <= 3) {
                // Treat as decimal mark.
                return '.' === $sep ? $string : str_replace(',', '.', $string);
            }
        }

        // Multiple occurrences, or > 3 trailing digits → thousands grouping.
        return str_replace($sep, '', $string);
    }
}
