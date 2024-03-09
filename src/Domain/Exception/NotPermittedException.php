<?php
    declare(strict_types=1);
    
    namespace Domain\Exception;
    
    use Exception, Throwable;

    /**
     * Исключение отсутствия прав
     */
    class NotPermittedException extends Exception
    {
        public function __construct(string $message = 'Нет доступа', int $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }