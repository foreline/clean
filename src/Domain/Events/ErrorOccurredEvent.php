<?php
    declare(strict_types=1);
    
    namespace Domain\Events;

    use Domain\Event\Event;
    use Domain\Event\EventInterface;

    /**
     * Событие возникновения ошибки
     */
    class ErrorOccurredEvent extends Event implements EventInterface
    {
        private string $error;
        private array $trace;
    
        /**
         * @param string $error
         */
        public function __construct(string $error = '')
        {
            $this->error = $error;
            $this->trace = debug_backtrace();
            
            parent::__construct();
        }
    
        /**
         * @return string
         */
        public function getError(): string
        {
            return $this->error;
        }
    
        /**
         * @return array
         */
        public function getTrace(): array
        {
            return $this->trace;
        }
        
    }