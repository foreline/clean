<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

/**
 * Strategy for handling event store cleanup after dispatch completion.
 *
 * Implementations decide what happens to completed event data:
 * - Immediate deletion (saves space, recommended for production)
 * - Retention for audit/compliance (delegates to scheduled batch purge)
 * - No-op (keep forever, rely on manual purge)
 */
interface EventPurgeStrategyInterface
{
    /**
     * Called when a dispatch row transitions to COMPLETED status.
     * Implementation should decide whether to purge the event data.
     *
     * @param int $eventStoreId The event store row ID (shared event body)
     * @param int $eventDispatchId The completed dispatch row ID
     */
    public function onDispatchCompleted(int $eventStoreId, int $eventDispatchId): void;
}
