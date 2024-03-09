<?php
    declare(strict_types=1);
    
    namespace Presentation\Helpers;
    
    /**
     * Класс для работы с датами
     */
    class Dates {

        /** @var array $months Месяцы */
        public static array $months = [
            '01'    => 'января',
            '02'    => 'февраля',
            '03'    => 'марта',
            '04'    => 'апреля',
            '05'    => 'мая',
            '06'    => 'июня',
            '07'    => 'июля',
            '08'    => 'августа',
            '09'    => 'сентября',
            '10'    => 'октября',
            '11'    => 'ноября',
            '12'    => 'декабря',
        ];

        /** @var array $arMonths Месяцы в именительном падеже */
        public static array $arMonths = [
            '1'     => 'январь',
            '01'    => 'январь',
            '2'     => 'февраль',
            '02'    => 'февраль',
            '3'     => 'март',
            '03'    => 'март',
            '4'     => 'апрель',
            '04'    => 'апрель',
            '5'     => 'май',
            '05'    => 'май',
            '6'     => 'июнь',
            '06'    => 'июнь',
            '7'     => 'июль',
            '07'    => 'июль',
            '8'     => 'август',
            '08'    => 'август',
            '9'     => 'сентябрь',
            '09'    => 'сентябрь',
            '10'    => 'октябрь',
            '11'    => 'ноябрь',
            '12'    => 'декабрь',
        ];

        /** @var array $weekDays Дни недели */
        public static array $weekDays = [
            0   => 'вс',
            1   => 'пн',
            2   => 'вт',
            3   => 'ср',
            4   => 'чт',
            5   => 'пт',
            6   => 'сб',
        ];

        /**
         * Парсит строку с датой и возвращает число,
         * представляющее собой количество секунд,
         * истекших с полуночи 1 января 1970 года GMT+0 до даты, указанной в строке
         *
         * @param string $date Строка с датой
         * @param bool $recursion
         * @return ?int $timestamp
         */

        public static function parse(string $date = '', bool $recursion = false): ?int
        {
            if ( 8 > strlen($date) ) {
                return null;
            }

            $date = trim($date);

            /// 31.01/2001
            //$pattern = '#^[0-3]{1}[1-9]{1}.\d{2}.\d{4}#';
            $pattern = '#\d{2}.\d{2}.\d{4}#';
            //$pattern = '#^\d{2}.\d{2}.\d{4}$#xismU';
            //$pattern = '#^[0-3]{1}\d{1}.[0-1]{1}\d{1}.\d{4}#';

            /// 2001-01.31
            //$pattern1 = '#^\d{4}.\d{2}.[0-3]{1}[1-9]{1}#';
            $pattern1 = '#\d{4}.\d{2}.\d{2}#';
            //$pattern1 = '#^\d{4}.\d{2}.\d{2}$#ximsU';
            //$pattern1 = '#^\d{4}.[0-1]{1}\d{1}.[0-3]{1}\d{1}#';

            //$pattern3 = '#^(\d{2,4}).(\d{2}).(\d{2,4})#';
            $pattern3 = '#^(\d{1,4}).(\d{1,2}).(\d{1,4})#';


            // 5/16/2013 2:58:18 PM
            $pattern4 = '#\d{1,4}[./-]\d{1,2}[./-]\d{2,4}([^0-9]\d{1,2}:\d{2}:\d{2})*#';

            if ( preg_match($pattern, $date) ) {
                $day    = (int)substr($date,0,2);
                $month  = (int)substr($date,3,2);
                $year   = (int)substr($date,6,4);
            } elseif ( preg_match($pattern1, $date) ) {
                $year   = (int)substr($date,0,4);
                $month  = (int)substr($date,5,2);
                $day    = (int)substr($date,8,2);
            } elseif ( !$recursion && preg_match($pattern3, $date, $matches)) {

                $nDate =    ( 10 > (int)$matches[1] ? '0' . (int)$matches[1] : (int)$matches[1])
                    . '.' .
                    ( 10 > (int)$matches[2] ? '0' . (int)$matches[2] : (int)$matches[2])
                    . '.' .
                    ( 10 > (int)$matches[3] ? '0' . (int)$matches[3] : (int)$matches[3]);
                return self::parse($nDate, true);
            } elseif ( preg_match($pattern4, $date, $matches)) {
                // @fixme
                return null;
            } else {
                return null;
            }

            if ( 19 === strlen($date) ) {
                $hour   = (int)substr($date, 11, 2);
                $min    = (int)substr($date, 14,2);
                $sec    = (int)substr($date, 17,2);
            } else {
                $hour   = 0;
                $min    = 0;
                $sec    = 0;
            }
            
            $mktime = mktime($hour, $min, $sec, $month, $day, $year);

            return false === $mktime ? null : $mktime;
        }

        /**
         * Возвращает разницу в днях между двумя датами
         *
         * @param string $dateFrom Дата с
         * @param string $dateTo Дата по
         *
         * @return int $fullDays Количество полных дней между датами
         */

        public static function dateDiff(string $dateFrom, string $dateTo): int
        {
            $timeStampFrom  = self::parse($dateFrom);
            $timeStampTo    = self::parse($dateTo);

            $dateDiff = $timeStampTo - $timeStampFrom;

            return (int)floor($dateDiff/(60*60*24));
        }

        /**
         * Возвращает разницу в днях между заданными датами. Разницей считается количество переходов через полночь.
         *
         * @param string $dateFrom Дата с
         * @param string $dateTo Дата по
         *
         * @return int $daysDiff
         */

        public static function daysDiff(string $dateFrom = '', string $dateTo = ''): int
        {
            $tsDateFrom = self::parse($dateFrom);
            $tsDateTo   = self::parse($dateTo);

            if ( (86400 > $tsDateTo - $tsDateFrom) && date('z', $tsDateFrom) === date('z', $tsDateTo)) {
                return 0;
            }

            return (int)ceil( abs(($tsDateTo - $tsDateFrom)/(24*60*60)) );
        }

        /**
         * Возвращает разницу в минутах между датами
         *
         * @param string $dateFrom Дата с
         * @param string $dateTo Дата по
         *
         * @return int $minDiff Разница в минутах между датами
         */

        public static function minDiff(string $dateFrom, string $dateTo): int
        {
            $timeStampFrom  = self::parse($dateFrom);
            $timeStampTo    = self::parse($dateTo);

            $dateDiff = $timeStampTo - $timeStampFrom;

            return (int)floor($dateDiff/60);
        }

        /**
         * Возвращает разницу в секундах между двумя датами
         *
         * @param string $dateFrom Дата с
         * @param string $dateTo Дата по
         *
         * @return int $secDiff Разница в секундах между датами
         */

        public static function secDiff(string $dateFrom, string $dateTo): int
        {
            $timeStampFrom  = self::parse($dateFrom);
            $timeStampTo    = self::parse($dateTo);

            return (int) ( $timeStampTo - $timeStampFrom );
        }

        /**
         * Выводит отформатированную дату "сегодня в 7:00 | вчера в 19:35 | 1 сентября"
         * Время выводится только для "сегодня" и "вчера", если задано
         *
         * @param string $date Исходная дата
         * @param bool $today [optional] Заменять ли дату на "сегодня" и "вчера", по умолчанию true
         * @param bool $weekDays [optional] Выводить ли дополнительно дни недели: 14 января, пн, по умолчанию false
         * @param bool $showMonth [optional] Выводить ли название месяца, по умолчанию true
         *
         * @return string $formattedDate отформатированная дата
         * @noinspection PhpTooManyParametersInspection
         */
        public static function dateFormat(string $date, bool $today = true, bool $weekDays = false, bool $showMonth = true): string
        {
            if ( empty($date) ) {
                return '';
            }

            $timeStamp = self::parse($date);

            $day    = date('j', $timeStamp);
            $month  = date('m', $timeStamp);
            $year   = date('Y', $timeStamp);

            // Определяем сегодняшнее ли это дата/время
            $isToday = false;
            $todayTimeStampStart = self::parse(date('Y.m.d 00:00:00'));
            $todayTimeStampEnd = self::parse(date('Y.m.d 23:59:59'));

            if ( $todayTimeStampStart < $timeStamp && $timeStamp < $todayTimeStampEnd ) {
                $isToday = true;
            }

            // Определяем вчерашние ли это дата/время
            $isYesterday = false;
            $yesterdayTimeStampStart = ( self::parse(date('Y.m.d 00:00:00')) - 86400 );
            $yesterdayTimeStampEnd = ( self::parse(date('Y.m.d 23:59:59')) - 86400 );

            if ( $yesterdayTimeStampStart < $timeStamp && $timeStamp < $yesterdayTimeStampEnd ) {
                $isYesterday = true;
            }

            /*
             * Форматируем вывод
             */

            if ( TRUE === $today && TRUE === $isToday ) {
                // сегодня
                $formattedDate = '<span>сегодня</span>';
            } elseif ( TRUE === $today && TRUE === $isYesterday ) {
                // вчера, в 18:16
                $formattedDate = '<span>вчера</span>';
            } else {
                $formattedDate = $day . ($showMonth ? '&nbsp;' . self::$months[$month] : '') . (date('Y') !== $year ? ' ' . $year : '');
            }

            if ( TRUE === $weekDays ) {
                $formattedDate .= ', ' . self::$weekDays[date('w', $timeStamp)];
            }

            return $formattedDate;
        }

        /**
         * Выводит отформатированную дату "сегодня в 7:00 | вчера в 19:35 | 1 сентября, 11:19"
         * Время выводится, если задано
         *
         * @param string $date Исходная дата
         * @param bool $today Заменять ли дату на "сегодня" и "вчера"
         * @param bool $weekDays Выводить ли дополнительно дни недели: 14 января 15:35, пн
         *
         * @return string $formattedDate
         */

        public static function dateTimeFormat(string $date, bool $today = true, bool $weekDays = false): string
        {
            if ( empty($date) ) {
                return '';
            }

            $timeStamp = self::parse($date);

            $day    = date('j', $timeStamp);
            $month  = date('m', $timeStamp);
            $year   = date('Y', $timeStamp);
            $hour   = date('H', $timeStamp);
            $min    = date('i', $timeStamp);
            //$sec    = date('s', $timeStamp);

            //$dateDiff = self::dateDiff($date, date('d.m.Y H:i:s'));

            // Определяем сегодняшнее ли это дата/время
            $isToday = false;
            $todayTimeStampStart = self::parse(date('Y.m.d 00:00:00'));
            $todayTimeStampEnd = self::parse(date('Y.m.d 23:59:59'));

            if ( $todayTimeStampStart < $timeStamp && $timeStamp < $todayTimeStampEnd ) {
                $isToday = true;
            }

            // Определяем вчерашние ли это дата/время
            $isYesterday = false;
            $yesterdayTimeStampStart = ( self::parse(date('Y.m.d 00:00:00')) - 86400 );
            $yesterdayTimeStampEnd = ( self::parse(date('Y.m.d 23:59:59')) - 86400 );

            if ( $yesterdayTimeStampStart < $timeStamp && $timeStamp < $yesterdayTimeStampEnd ) {
                $isYesterday = true;
            }

            // Форматируем вывод
            if ( TRUE === $today && TRUE === $isToday ) {
                // сегодня, в 18:16
                $formattedDate = '<span>сегодня</span>' . ( 0 < strlen($hour) && 0 < strlen($min) ? ' в&nbsp;' . $hour . ':' . $min : '');
            } elseif ( TRUE === $today && TRUE === $isYesterday ) {
                // вчера, в 18:16
                $formattedDate = '<span>вчера</span>' . ( 0 < strlen($hour) && 0 < strlen($min) ? ' в&nbsp;' . $hour . ':' . $min : '');
            } else {
                $formattedDate = $day . '&nbsp;' . self::$months[$month]
                    . ( date('Y') !== $year ? ' ' . $year : '' )
                    . ( 0 < strlen($hour) && 0 < strlen($min) ? ', '  . $hour . ':' . $min : '');
            }

            if ( TRUE === $weekDays ) {
                $formattedDate .= ', ' . self::$weekDays[date('w', $timeStamp)];
            }

            return $formattedDate;
        }

        /**
         * Возвращает время в формате чч:мм из минут
         *
         * @param int $minutes минуты
         * @return string $timeFormat
         */

        public static function timeFromMinutes(int $minutes, $showDays = false): string
        {
            if ( 0 >= $minutes ) {
                return '00:00';
            }

            $hours  = floor($minutes/60);
            $min    = round($minutes - ($hours * 60));

            $output = '';

            if ( TRUE === $showDays ) {

                if ( 24 <= $hours ) {
                    $days = floor($hours/24);
                    $hours = $hours - 24*$days;
                } else {
                    $days = 0;
                }

                if ( 0 < $days ) {
                    $output .= $days . 'дн.&nbsp;';
                }
            }

            $output .= (10 > $hours ? '0' : '') . $hours . ':' . (10 > $min ? '0' : '') . $min;

            return $output;
        }

        /**
         * Возвращает время в формате мм:сс из секунд
         *
         * @param int $seconds минуты
         * @return string $timeFormat
         */

        public static function timeFromSeconds(int $seconds, $showHours = false): string
        {
            if ( 0 >= $seconds ) {
                return '00:00';
            }

            $min    = floor($seconds/60);
            $sec    = round($seconds - ($min * 60));

            $output = '';

            if ( TRUE === $showHours ) {

                if ( 60 <= $min ) {
                    $hours = floor($min/60);
                    $min -= floor($hours * 60);
                } else {
                    $hours = 0;
                }

                if ( 0 < $hours ) {
                    $output .= $hours . 'ч.&nbsp;';
                }
            }

            $output .= '<span title="ч. мм:сс">' . (10 > $min ? '0' : '') . $min . ':' . (10 > $sec ? '0' : '') . $sec . '</span>';

            return $output;
        }

        /**
         * Тестирование различных форматов дат
         * @return void
         */
        public static function test(): void
        {

            /*
            // ДД.ММ.ГГГГ
            $date = '31.01.2001';
            $date = '31/01/2001';
            $date = '31-01-2001';
    
            // ДД.М.ГГГГ
            $date = '31/1/2001';
            $date = '31.1.2001';
            $date = '31-1-2001';
    
            // Д.М.ГГГГ
            $date = '1/1/2001';
            $date = '1.1.2001';
            $date = '1-1-2001';
    
            // ГГГГ.ММ.ДД
            $date = '1991.12.31';
            $date = '1991/12/31';
            $date = '1991-12-31';
    
            // ГГГГ.ММ.Д
            $date = '1991.12.1';
            $date = '1991/12/1';
            $date = '1991-12-1';
    
            // ГГГГ.М.Д
            $date = '1991.2.1';
            $date = '1991/2/1';
            $date = '1991-2-1';
    
            // ГГГГ.М.ДД
            $date = '1991.2.28';
            $date = '1991/2/28';
            $date = '1991-2-28';
    
            // ДД.ММ.ГГ
            $date = '31.01.98';
            $date = '31/01/98';
            $date = '31-01-98';
            */
        }

    }
