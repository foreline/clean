<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

use DateTimeInterface;
use Domain\Event\Enum\EventStoreStatusEnum;

/**
 * Single event store entry for monitoring display.
 */
class EventStoreEntry
{
    /** @var int Идентификатор */
    private int $id;
    
    /** @var string Класс события */
    private string $eventClass;
    
    /** @var string Класс подписчика */
    private string $subscriberClass;
    
    /** @var EventStoreStatusEnum Статус */
    private EventStoreStatusEnum $status;
    
    /** @var int Количество попыток */
    private int $attempts;
    
    /** @var ?string Текст ошибки */
    private ?string $error;
    
    /** @var DateTimeInterface Время возникновения события */
    private DateTimeInterface $occurredOn;
    
    /** @var ?DateTimeInterface Время обработки */
    private ?DateTimeInterface $processedAt;
    
    /** @var ?DateTimeInterface Время начала обработки */
    private ?DateTimeInterface $processingStartedAt;
    
    /** @var ?DateTimeInterface Время создания записи */
    private ?DateTimeInterface $dateCreated;
    
    public function __construct(
        int $id,
        string $eventClass,
        string $subscriberClass,
        EventStoreStatusEnum $status,
        int $attempts,
        ?string $error,
        DateTimeInterface $occurredOn,
        ?DateTimeInterface $processedAt,
        ?DateTimeInterface $processingStartedAt,
        ?DateTimeInterface $dateCreated
    ) {
        $this->id                  = $id;
        $this->eventClass          = $eventClass;
        $this->subscriberClass     = $subscriberClass;
        $this->status              = $status;
        $this->attempts            = $attempts;
        $this->error               = $error;
        $this->occurredOn          = $occurredOn;
        $this->processedAt         = $processedAt;
        $this->processingStartedAt = $processingStartedAt;
        $this->dateCreated         = $dateCreated;
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getEventClass(): string
    {
        return $this->eventClass;
    }
    
    public function getSubscriberClass(): string
    {
        return $this->subscriberClass;
    }
    
    public function getStatus(): EventStoreStatusEnum
    {
        return $this->status;
    }
    
    public function getAttempts(): int
    {
        return $this->attempts;
    }
    
    public function getError(): ?string
    {
        return $this->error;
    }
    
    public function getOccurredOn(): DateTimeInterface
    {
        return $this->occurredOn;
    }
    
    public function getProcessedAt(): ?DateTimeInterface
    {
        return $this->processedAt;
    }
    
    public function getProcessingStartedAt(): ?DateTimeInterface
    {
        return $this->processingStartedAt;
    }
    
    public function getDateCreated(): ?DateTimeInterface
    {
        return $this->dateCreated;
    }
    
    /**
     * Short class name without namespace prefix.
     */
    public function getShortEventClass(): string
    {
        $parts = explode('\\', $this->eventClass);
        return end($parts);
    }
    
    /**
     * Short subscriber class name without namespace prefix.
     */
    public function getShortSubscriberClass(): string
    {
        $parts = explode('\\', $this->subscriberClass);
        return end($parts);
    }
    
    /**
     * Total end-to-end duration in seconds (from dateCreated to processedAt).
     * Includes queue wait time + execution time.
     * Returns null if either timestamp is missing.
     */
    public function getEndToEndDurationSeconds(): ?float
    {
        if ( null === $this->processedAt || null === $this->dateCreated ) {
            return null;
        }
        
        return (float)($this->processedAt->getTimestamp() - $this->dateCreated->getTimestamp());
    }
    
    /**
     * Queue wait time in seconds (from dateCreated to processingStartedAt).
     * How long the event waited before the worker picked it up.
     * Returns null if either timestamp is missing.
     */
    public function getQueueWaitSeconds(): ?float
    {
        if ( null === $this->processingStartedAt || null === $this->dateCreated ) {
            return null;
        }
        
        return (float)($this->processingStartedAt->getTimestamp() - $this->dateCreated->getTimestamp());
    }
    
    /**
     * Actual execution time in seconds (from processingStartedAt to processedAt).
     * How long the subscriber took to execute.
     * Returns null if either timestamp is missing.
     */
    public function getExecutionTimeSeconds(): ?float
    {
        if ( null === $this->processedAt || null === $this->processingStartedAt ) {
            return null;
        }
        
        return (float)($this->processedAt->getTimestamp() - $this->processingStartedAt->getTimestamp());
    }
    
    /**
     * @deprecated Use getEndToEndDurationSeconds() instead.
     * Processing duration in seconds (from dateCreated to processedAt).
     * Returns null if either timestamp is missing.
     */
    public function getProcessingDurationSeconds(): ?float
    {
        return $this->getEndToEndDurationSeconds();
    }
}
