<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Interface for async subscribers that define a custom retry policy.
 *
 * Subscribers implementing this interface can override the global default
 * retry strategy with per-subscriber configuration (e.g., more aggressive
 * retries for critical integrations like Zabbix, fail-fast for cache invalidation).
 *
 * Subscribers without this interface use the global default RetryPolicy
 * configured on the AsyncEventMessageHandler.
 *
 * @example never give up, retry every hour max:
 *
 *   class NeverGiveUpSubscriber implements AsyncSubscriberInterface, RetryableSubscriberInterface
 *   {
 *       public function getRetryPolicy(): RetryPolicy
 *       {
 *           return new RetryPolicy(
 *               maxRetries: 0,          // Never give up
 *               baseDelayMs: 30000,     // Start at 30s
 *               multiplier: 2.0,
 *               maxDelayMs: 3600000,    // Cap at 1 hour
 *           );
 *       }
 *   }
 */
interface RetryableSubscriberInterface extends AsyncSubscriberInterface
{
    /**
     * Returns the retry policy for this subscriber.
     *
     * @return RetryPolicy
     */
    public function getRetryPolicy(): RetryPolicy;
}
