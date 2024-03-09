<?php
    declare(strict_types=1);
    
    namespace Presentation\Helpers;

    /**
     *
     */
    class Utils
    {
        /**
         * Находит ссылки в тексте и преобразует их в html-тег <a href="...">
         *
         * @param string $text
         * @param array $classes Список классов, которые включить в ссылку
         * @return string $text
         */
        static function parseUrl(string $text = '', array $classes = []): string
        {
            if ( empty($text) ) {
                return '';
            }
            
            if ( 0 < count($classes) ) {
                $classList = implode(' ', $classes);
            } else {
                $classList = '';
            }
            
            // Обычные ссылки
            preg_match_all('#(https?://[0-9a-z._?&=/:%\{\}\-]+)#xim', $text, $matches);
        
            if ( is_array($matches) && 0 < count($matches) ) {
                foreach ( $matches[1] as $match ) {
                    if ( !$url = parse_url($match) ) {
                        continue;
                    }
                    
                    $host = $match;
                    if ( array_key_exists('host', $url) ) {
                        $host = $url['host'];
                    }
                    
                    $link = '<a href="' . $match . '" target="_blank" ' . (!empty($classList) ? ' class="' . $classList . '" ' : '') . ' >' . $host . '</a>';
                
                    $text = str_replace($match, $link, $text);
                }
            }
            
            // Е-мейлы
            preg_match_all('#([\w\-.]+@[\w\-]+\.\w{2,10})#xim', $text, $matches);
            if ( is_array($matches) && 0 < count($matches) ) {
                foreach ( $matches[1] as $match ) {
                    $emailLink = '<a href="mailto:' . $match . '" target="_blank">' . $match . '</a>';
                    $text = str_replace($match, $emailLink, $text);
                }
            }
            
            return $text;
        }
    
        /**
         * Функция для человеческого отображения размера данных
         *
         * @param int $size
         * @param string $separator
         * @return string $result
         */
        static function humanFSize(int $size, string $separator = '&nbsp;'): string
        {
            $fileSizeName = ['Байт', 'Кб', 'Мб', 'Гб', 'Тб'];
        
            if ( 0 >= $size ) {
                return '0' . $separator . $fileSizeName[0];
            }
        
            return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $separator . $fileSizeName[$i];
        }
    
        /**
         * Склоняет числительные
         *
         * @param int $number Число
         * @param array $titles Массив, описывающий числительные. ['(одна)штука', '(две)штуки', '(много)штук'];
         * @param bool $showNumber Отображать ли число перед числительным
         *
         * @return string
         */
        static function declOfNum(int $number = 0, array $titles = [], bool $showNumber = false): string
        {
            $cases = [2, 0, 1, 1, 1, 2];
            return ($showNumber ? $number . ' ' : '') . $titles[ ($number%100 > 4 && $number%100 < 20)? 2 : $cases[min($number%10, 5)] ];
        }
    
        /**
         * Среднее медиана
         *
         * @param array $arData Массив с данными для усреднения
         * @return float|int $mediana
         */
        static function mediana(array $arData = []): float|int
        {
            sort($arData, SORT_NUMERIC);
        
            if ( 0 === count($arData) % 2 ) {
                // Четное число значений
                $mediana = ($arData[(count($arData)/2 - 1)] + $arData[count($arData)/2] ) / 2;
            } else {
                // Нечетное число значений
                $mediana = $arData[ceil(count($arData)/2)];
            }
        
            return $mediana;
        }
    }