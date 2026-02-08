<?php
declare(strict_types=1);

namespace Domain\Scheduler;

use DateTimeImmutable;

/**
 * Interface for scheduled tasks
 */
interface TaskInterface
{
    /**
     * Get the task name/identifier
     */
    public function getName(): string;
    
    /**
     * Get the cron expression for this task
     */
    public function getCronExpression(): string;
    
    /**
     * Override the cron expression for this task
     */
    public function setCronExpression(string $cronExpression): void;
    
    /**
     * Execute the task
     */
    public function execute(): void;
    
    /**
     * Get the last execution time
     */
    public function getLastExecutedAt(): ?DateTimeImmutable;
    
    /**
     * Set the last execution time
     */
    public function setLastExecutedAt(DateTimeImmutable $time): void;
    
    /**
     * Check if the task is enabled
     */
    public function isEnabled(): bool;
    
    /**
     * Get task priority (higher number = higher priority)
     */
    public function getPriority(): int;
    
    /**
     * Whether this task requires an exclusive lock to prevent concurrent execution.
     * Tasks returning true will be skipped if another instance is already running.
     */
    public function requiresLock(): bool;
    
    /**
     * Maximum expected execution time in seconds.
     * Used for diagnostics and stale lock detection.
     */
    public function getLockTimeout(): int;
}