<?php
    declare(strict_types=1);
    
    namespace Domain\Exception;
    
    use Exception, Throwable;

    /**
     * Исключение неавторизованного пользователя
     */
    class NotAuthorizedException extends Exception
    {
        public function __construct(string $message = 'Требуется авторизация', int $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }