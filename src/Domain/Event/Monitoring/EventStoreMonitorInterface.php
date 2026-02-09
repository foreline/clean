<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

use DateTimeInterface;

/**
 * Interface for monitoring and managing the Event Store.
 *
 * Provides read-only monitoring capabilities (status, throughput, health)
 * and operational actions (purge completed, retry failed).
 *
 * Implementations are storage-agnostic — the interface can be backed by
 * a database, file system, message queue, or any other persistence mechanism.
 */
interface EventStoreMonitorInterface
{
    /**
     * Get aggregate counts of events grouped by status.
     */
    public function getStatusSummary(): EventStoreStatusSummary;
    
    /**
     * Get failed events with error details, ordered by most recent first.
     */
    public function getFailedEvents(int $limit = 50, int $offset = 0): EventStoreEntryCollection;
    
    /**
     * Get events stuck in 'processing' state longer than the threshold.
     *
     * @param int $thresholdSeconds Consider processing events older than this as stuck (default: 5 minutes)
     */
    public function getStuckEvents(int $thresholdSeconds = 300): EventStoreEntryCollection;
    
    /**
     * Get the most recent events regardless of status, ordered by most recent first.
     */
    public function getRecentEvents(int $limit = 50): EventStoreEntryCollection;
    
    /**
     * Get throughput statistics (events per time period, avg processing time).
     */
    public function getThroughput(): EventStoreThroughput;
    
    /**
     * Get overall health assessment based on current metrics.
     *
     * @param int $stuckThresholdSeconds Threshold for considering events as stuck (default: 5 minutes)
     */
    public function getHealth(int $stuckThresholdSeconds = 300): EventStoreHealth;
    
    /**
     * Delete completed events older than the given date.
     * If $before is null, deletes ALL completed events.
     *
     * @return int Number of deleted events
     */
    public function purgeCompleted(?DateTimeInterface $before = null): int;
    
    /**
     * Reset failed events back to 'pending' for reprocessing.
     * If $eventId is null, retries ALL failed events.
     *
     * @return int Number of events reset
     */
    public function retryFailed(?int $eventId = null): int;
}
