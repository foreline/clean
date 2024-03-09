<?php
    declare(strict_types=1);
    
    namespace Domain\Exception;
    
    use Exception, Throwable;

    /**
     * Исключение не найденных объектов
     */
    class NotFoundException extends Exception
    {
        public function __construct(string $message = 'Элемент не найден', int $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }