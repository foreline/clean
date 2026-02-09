<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

/**
 * Throughput statistics for the Event Store.
 */
class EventStoreThroughput
{
    /** @var int Обработано за последнюю минуту */
    private int $completedLastMinute;
    
    /** @var int Обработано за последний час */
    private int $completedLastHour;
    
    /** @var int Обработано за последние 24 часа */
    private int $completedLast24Hours;
    
    /** @var int Ошибок за последний час */
    private int $failedLastHour;
    
    /** @var ?float Среднее время выполнения подписчика в секундах */
    private ?float $avgExecutionTimeSeconds;
    
    /** @var ?float Среднее время ожидания в очереди в секундах */
    private ?float $avgQueueWaitSeconds;
    
    /** @var ?float Среднее сквозное время в секундах */
    private ?float $avgEndToEndSeconds;
    
    public function __construct(
        int $completedLastMinute,
        int $completedLastHour,
        int $completedLast24Hours,
        int $failedLastHour,
        ?float $avgExecutionTimeSeconds,
        ?float $avgQueueWaitSeconds = null,
        ?float $avgEndToEndSeconds = null
    ) {
        $this->completedLastMinute     = $completedLastMinute;
        $this->completedLastHour       = $completedLastHour;
        $this->completedLast24Hours    = $completedLast24Hours;
        $this->failedLastHour          = $failedLastHour;
        $this->avgExecutionTimeSeconds = $avgExecutionTimeSeconds;
        $this->avgQueueWaitSeconds     = $avgQueueWaitSeconds;
        $this->avgEndToEndSeconds      = $avgEndToEndSeconds;
    }
    
    public function getCompletedLastMinute(): int
    {
        return $this->completedLastMinute;
    }
    
    public function getCompletedLastHour(): int
    {
        return $this->completedLastHour;
    }
    
    public function getCompletedLast24Hours(): int
    {
        return $this->completedLast24Hours;
    }
    
    public function getFailedLastHour(): int
    {
        return $this->failedLastHour;
    }
    
    /**
     * Average subscriber execution time (processing_started_at → processed_at).
     */
    public function getAvgExecutionTimeSeconds(): ?float
    {
        return $this->avgExecutionTimeSeconds;
    }
    
    /**
     * Average queue wait time (date_created → processing_started_at).
     */
    public function getAvgQueueWaitSeconds(): ?float
    {
        return $this->avgQueueWaitSeconds;
    }
    
    /**
     * Average end-to-end time (date_created → processed_at).
     */
    public function getAvgEndToEndSeconds(): ?float
    {
        return $this->avgEndToEndSeconds;
    }
    
    /**
     * @deprecated Use getAvgExecutionTimeSeconds() instead.
     */
    public function getAvgProcessingTimeSeconds(): ?float
    {
        return $this->avgExecutionTimeSeconds;
    }
}
