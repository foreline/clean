<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

/**
 * Aggregate counts of events grouped by status.
 */
class EventStoreStatusSummary
{
    /** @var int Ожидающие обработки */
    private int $pending;
    
    /** @var int Обрабатываемые в данный момент */
    private int $processing;
    
    /** @var int Успешно обработанные */
    private int $completed;
    
    /** @var int Завершённые с ошибкой */
    private int $failed;
    
    public function __construct(
        int $pending,
        int $processing,
        int $completed,
        int $failed
    ) {
        $this->pending    = $pending;
        $this->processing = $processing;
        $this->completed  = $completed;
        $this->failed     = $failed;
    }
    
    public function getPending(): int
    {
        return $this->pending;
    }
    
    public function getProcessing(): int
    {
        return $this->processing;
    }
    
    public function getCompleted(): int
    {
        return $this->completed;
    }
    
    public function getFailed(): int
    {
        return $this->failed;
    }
    
    public function getTotal(): int
    {
        return $this->pending + $this->processing + $this->completed + $this->failed;
    }
}
