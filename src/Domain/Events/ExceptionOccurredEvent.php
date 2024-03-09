<?php
    declare(strict_types=1);
    
    namespace Domain\Events;

    use Domain\Event\Event;
    use Domain\Event\EventInterface;
    use Throwable;

    /**
     * Событие возникновения исключения
     */
    class ExceptionOccurredEvent extends Event implements EventInterface
    {
        private Throwable $exception;
        private array $trace;
    
        /**
         * @param Throwable $exception
         */
        public function __construct(Throwable $exception)
        {
            $this->exception = $exception;
            $this->trace = debug_backtrace();
            
            parent::__construct();
        }
    
        /**
         * @return Throwable
         */
        public function getException(): Throwable
        {
            return $this->exception;
        }
    
        /**
         * @return array
         */
        public function getTrace(): array
        {
            return $this->trace;
        }
    
    }