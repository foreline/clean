<?php
    declare(strict_types=1);
    
    namespace Domain\Exception;
    
    use Exception, Throwable;

    /**
     * Исключение валидации
     */
    class ValidationException extends Exception
    {
        /** @var ?string[]  */
        private ?array $errors;
        
        public function __construct(string $message = 'Ошибка валидации', int $code = 0, Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    
        /**
         * @return ?string[]
         */
        public function getErrors(): ?array
        {
            return $this->errors;
        }
    
        /**
         * @param ?string[] $errors
         * @return ValidationException
         */
        public function setErrors(?array $errors): ValidationException
        {
            $this->errors = $errors;
            return $this;
        }
    }