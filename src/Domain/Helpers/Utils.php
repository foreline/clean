<?php
declare(strict_types=1);

namespace Domain\Helpers;

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
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
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
        $str = str_replace('_', '', ucwords($string, '_'));
        
        if ( !$capitalizeFirstCharacter ) {
            $str = lcfirst($str);
        }
        
        return $str;
    }
}