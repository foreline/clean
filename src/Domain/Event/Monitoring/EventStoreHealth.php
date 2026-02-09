<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

/**
 * Health assessment of the Event Store system.
 *
 * Determines overall health status based on:
 * - Failed event count
 * - Stuck (processing too long) event count
 * - Queue depth (pending events)
 * - Throughput (events processed per time period)
 */
class EventStoreHealth
{
    /** @var HealthStatus Статус здоровья */
    private HealthStatus $status;
    
    /** @var EventStoreStatusSummary Сводка по статусам */
    private EventStoreStatusSummary $summary;
    
    /** @var EventStoreThroughput Пропускная способность */
    private EventStoreThroughput $throughput;
    
    /** @var int Количество зависших событий */
    private int $stuckCount;
    
    public function __construct(
        EventStoreStatusSummary $summary,
        EventStoreThroughput $throughput,
        int $stuckCount
    ) {
        $this->summary    = $summary;
        $this->throughput = $throughput;
        $this->stuckCount = $stuckCount;
        $this->status     = $this->determineStatus();
    }
    
    public function getStatus(): HealthStatus
    {
        return $this->status;
    }
    
    public function getSummary(): EventStoreStatusSummary
    {
        return $this->summary;
    }
    
    public function getThroughput(): EventStoreThroughput
    {
        return $this->throughput;
    }
    
    public function getStuckCount(): int
    {
        return $this->stuckCount;
    }
    
    public function isHealthy(): bool
    {
        return HealthStatus::HEALTHY === $this->status;
    }
    
    public function isDegraded(): bool
    {
        return HealthStatus::DEGRADED === $this->status;
    }
    
    public function isCritical(): bool
    {
        return HealthStatus::CRITICAL === $this->status;
    }
    
    /**
     * Determine health status based on current metrics.
     *
     * CRITICAL:
     *   - Any events stuck in processing
     *   - More than 10 failed events
     *
     * DEGRADED:
     *   - Any failed events
     *   - Queue depth > 50 (pending events accumulating)
     *   - Failed events in the last hour
     *
     * HEALTHY:
     *   - No issues detected
     */
    private function determineStatus(): HealthStatus
    {
        // Critical: stuck events or many failures
        if (0 < $this->stuckCount || 10 < $this->summary->getFailed()) {
            return HealthStatus::CRITICAL;
        }
        
        // Degraded: any failures or growing queue
        if (
            0 < $this->summary->getFailed()
            || 50 < $this->summary->getPending()
            || 0 < $this->throughput->getFailedLastHour()
        ) {
            return HealthStatus::DEGRADED;
        }
        
        return HealthStatus::HEALTHY;
    }
}
