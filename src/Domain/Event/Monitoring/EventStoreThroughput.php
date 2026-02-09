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
    
    /** @var ?float Среднее время обработки в секундах */
    private ?float $avgProcessingTimeSeconds;
    
    public function __construct(
        int $completedLastMinute,
        int $completedLastHour,
        int $completedLast24Hours,
        int $failedLastHour,
        ?float $avgProcessingTimeSeconds
    ) {
        $this->completedLastMinute      = $completedLastMinute;
        $this->completedLastHour        = $completedLastHour;
        $this->completedLast24Hours     = $completedLast24Hours;
        $this->failedLastHour           = $failedLastHour;
        $this->avgProcessingTimeSeconds = $avgProcessingTimeSeconds;
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
    
    public function getAvgProcessingTimeSeconds(): ?float
    {
        return $this->avgProcessingTimeSeconds;
    }
}
