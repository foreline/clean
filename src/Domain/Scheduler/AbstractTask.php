<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use DateTimeImmutable;

/**
 * Abstract base class for scheduled tasks
 */
abstract class AbstractTask implements TaskInterface
{
    private ?DateTimeImmutable $lastExecutedAt = null;
    
    public function __construct(
        private readonly string $name,
        private readonly string $cronExpression,
        private readonly bool $enabled = true,
        private readonly int $priority = 0
    ) {
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }
    
    public function getLastExecutedAt(): ?DateTimeImmutable
    {
        return $this->lastExecutedAt;
    }
    
    public function setLastExecutedAt(DateTimeImmutable $time): void
    {
        $this->lastExecutedAt = $time;
    }
    
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    public function getPriority(): int
    {
        return $this->priority;
    }
    
    /**
     * Abstract method that must be implemented by concrete tasks
     */
    abstract public function execute(): void;
}
