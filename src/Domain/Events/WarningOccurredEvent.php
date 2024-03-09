<?php
    declare(strict_types=1);
    
    namespace Domain\Events;

    use Domain\Event\Event;

    /**
     * Событие возникновения предупреждения
     */
    class WarningOccurredEvent extends Event
    {
        private string $warning;
        private array $trace;
    
        /**
         * @param string $warning
         */
        public function __construct(string $warning)
        {
            $this->warning = $warning;
            $this->trace = debug_backtrace();
            
            parent::__construct();
        }
    
        /**
         * @return string
         */
        public function getWarning(): string
        {
            return $this->warning;
        }
    
        /**
         * @return array
         */
        public function getTrace(): array
        {
            return $this->trace;
        }
    
    }