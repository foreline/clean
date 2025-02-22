<?php
declare(strict_types=1);

namespace Domain\Helpers;

/**
 *
 */
class Utils
{
    /**
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
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public static function snakeToCamel(string $string, bool $capitalizeFirstCharacter = false): string
    {
        //$string = mb_strtolower($string);
        
        $result = str_replace('_', '', ucwords($string, '_'));
        
        if ( !$capitalizeFirstCharacter ) {
            $result = lcfirst($result);
        }
        
        return $result;
    }
    
    /**
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