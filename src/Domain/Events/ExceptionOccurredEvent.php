<?php
declare(strict_types=1);

namespace Domain\Events;

use Domain\Event\Event;
use Domain\Event\EventInterface;
use Throwable;

/**
 * Exception Occurred Event
 */
class ExceptionOccurredEvent extends Event implements EventInterface
{
    private Throwable $exception;
    private array $trace;
    private mixed $data;
    
    /**
     * @param Throwable $exception
     * @param mixed|null $data
     */
    public function __construct(Throwable $exception, mixed $data = null)
    {
        $this->exception = $exception;
        //$this->trace = debug_backtrace();
        $this->trace = $exception->getTrace();
        $this->data = $data;
        
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
    
    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }
}