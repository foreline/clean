<?php
declare(strict_types=1);

namespace Domain\Helpers;

/**
 * Utility class for string transformations, such as converting between camelCase, PascalCase, and snake_case.
 */
class Utils
{
    /**
     * Converts camelCase or PascalCase to snake_case.
     * For example, "camelCase" becomes "camel_case", and "PascalCase" becomes "pascal_case".
     * If the input string contains consecutive uppercase letters, they are treated as a single word. For example,
     * "XMLHttpRequest" becomes "xml_http_request".
     *
     * @param string $input
     * @param bool $strtoupper
     * @return string
     */
    public static function camelToSnake(string $input, bool $strtoupper = false): string
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ( $ret as &$match ) {
            $match = $match == mb_strtoupper($match) ? mb_strtolower($match) : lcfirst($match);
        }
        $result = implode('_', $ret);
        
        if ( $strtoupper ) {
            $result = mb_strtoupper($result);
        }
        
        return $result;
    }
    
    /**
     * Converts PascalCase to snake_case.
     * For example, "PascalCase" becomes "pascal_case".
     * If the input string contains consecutive uppercase letters, they are treated as a single word. For example,
     * "XMLHttpRequest" becomes "xml_http_request".
     *
     * @param string $input
     * @param bool $strtoupper
     * @return string
     */
    public static function pascalToSnake(string $input, bool $strtoupper = false): string
    {
        return self::camelToSnake($input, $strtoupper);
    }
    
    /**
     * Converts snake_case to camelCase.
     * For example, "snake_case" becomes "snakeCase". If the second parameter is set to true,
     * the first character will be capitalized, resulting in "SnakeCase".
     * If the input string contains consecutive underscores, they are treated as a single separator. For example,
     * "snake__case" becomes "snakeCase" or "SnakeCase".
     *
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public static function snakeToCamel(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $string = mb_strtolower($string);
        
        $result = str_replace('_', '', ucwords($string, '_'));
        
        if ( !$capitalizeFirstCharacter ) {
            $result = lcfirst($result);
        }
        
        return $result;
    }
    
    /**
     * Converts snake_case to PascalCase.
     * For example, "snake_case" becomes "SnakeCase". If the input string contains consecutive underscores,
     * they are treated as a single separator. For example, "snake__case" becomes "SnakeCase".
     *
     * @param string $string
     * @return string
     */
    public static function snakeToPascal(string $string): string
    {
        return self::snakeToCamel($string, true);
    }
    
    /**
     * Converts the first character of a string to lowercase, taking into account multibyte characters.
     * If the second character of the string is uppercase, the original string is returned unchanged. For example,
     * "XMLHttpRequest" remains "XMLHttpRequest", while "HelloWorld" becomes "helloWorld".
     *
     * @param string $string
     * @return string
     */
    public static function lcfirst(string $string): string
    {
        if ( empty($string) ) {
            return '';
        }
        
        $second = mb_substr($string, 1, 1);
        
        if ( mb_strtoupper($second) === $second ) {
            return $string;
        }
        
        if ( function_exists('mb_lcfirst') ) {
            return mb_lcfirst($string);
        }
        
        return lcfirst($string);
    }
}