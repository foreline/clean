<?php
declare(strict_types=1);

namespace Domain\Scheduler;

/**
 * Message wrapper for tasks in the scheduler
 */
final class TaskMessage
{
    public function __construct(
        private readonly string $taskName,
        private readonly array $payload = []
    ) {
    }
    
    public function getTaskName(): string
    {
        return $this->taskName;
    }
    
    public function getPayload(): array
    {
        return $this->payload;
    }
}
