<?php
    declare(strict_types=1);
    
    namespace Presentation\Helpers;

    /**
     * @deprecated
     */
    class Asset
    {
        /**
         * Выводит script тег, (или возвращает, см. второй параметр) добавляя в url filemtime метку
         *
         * @usage Asset::jsInc('/js/script.js');
         * @param string $file Абсолютный путь к js файлу
         * @param bool $return Если передано и значение равно TRUE, функция вернет результат вместо его вывода.
         * @return string|null $output
         */
        public static function addJs(string $file, bool $return = false): ?string
        {
            if ( empty($file) ) {
                return null;
            }
        
            // For Windows hosting
            $file = str_replace('\\', '/', $file);
            $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $file);
        
            if ( !str_starts_with($file, '/') ) {
                // Если задан относительный путь
                $filePath = $_SERVER['DOCUMENT_ROOT'] . preg_replace('#([^/])+$#', '', $_SERVER['REQUEST_URI']) . $file;
            } else {
                // Если задан абсолютный путь
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
            }
        
            $fileSrc = str_replace('.js', '.js?' . filemtime($filePath), $file);
        
            $fileSrc = str_replace('//', '/', $fileSrc);
        
            $output = '<script src="' . $fileSrc . '"></script>' . "\n";
        
            if ( $return ) {
                return $output;
            }
        
            echo $output;
            
            return null;
        }
    
        /**
         * Выводит [link type="text/css"] тег, (или возвращает, см. второй параметр) добавляя в url filemtime метку
         *
         * @usage Asset::cssInc('/css/style.css');
         * @param string $file Абсолютный путь к css файлу
         * @param bool $return Если передано и значение равно TRUE, функция вернет результат вместо его вывода.
         * @return string|null
         */
        public static function addCss(string $file, bool $return = false): ?string
        {
            if ( empty($file) ) {
                return null;
            }
        
            // For Windows hosting
            $file = str_replace('\\', '/', $file);
            $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $file);
        
            if ( !str_starts_with($file, '/') ) {
                // Если задан относительный путь
                $filePath = $_SERVER['DOCUMENT_ROOT'] . preg_replace('#([^/])+$#', '', $_SERVER['REQUEST_URI']) . $file;
            } else {
                // Если задан абсолютный путь
                $filePath = $_SERVER['DOCUMENT_ROOT'] . $file;
            }
        
            $fileSrc = str_replace('.css', '.css?' . filemtime($filePath), $file);
        
            $fileSrc = str_replace('//', '/', $fileSrc);
        
            $output = '<link href="' . $fileSrc . '" rel="stylesheet" type="text/css" />' . "\n";
        
            if ( $return ) {
                return $output;
            }
        
            echo $output;
            
            return null;
        }
    }