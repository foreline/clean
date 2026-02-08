<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Marker interface for subscribers that should be dispatched asynchronously.
 *
 * Subscribers implementing this interface will not block the Publisher::publish() call.
 * Instead, they will be enqueued for deferred processing by the async worker
 * via the configured AsyncDispatcherInterface implementation.
 *
 * Requires AsyncDispatcherInterface to be set on the Publisher instance.
 * If no async dispatcher is configured, the subscriber will fall back to synchronous processing.
 */
interface AsyncSubscriberInterface extends SubscriberInterface
{
}
