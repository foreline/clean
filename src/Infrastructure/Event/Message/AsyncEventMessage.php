<?php
declare(strict_types=1);

namespace Infrastructure\Event\Message;

/**
 * Symfony Messenger message for async event processing.
 *
 * Carries the serialized event payload and target subscriber class name
 * through the message transport for deferred processing by the worker.
 *
 * Two IDs track the normalized storage:
 *   - eventDispatchId: row in event_dispatch table (subscriber-specific, status tracking)
 *   - eventStoreId: row in event_store table (shared event body)
 */
final class AsyncEventMessage
{
    /** @var string Event FQCN */
    private string $eventClass;
    
    /** @var string Serialized event payload */
    private string $serializedEvent;
    
    /** @var string Subscriber FQCN */
    private string $subscriberClass;
    
    /** @var int Dispatch queue record ID (event_dispatch table) */
    private int $eventDispatchId;
    
    /** @var int Event store record ID (event_store table, shared body) */
    private int $eventStoreId;
    
    public function __construct(
        string $eventClass,
        string $serializedEvent,
        string $subscriberClass,
        int $eventDispatchId,
        int $eventStoreId,
    ) {
        $this->eventClass = $eventClass;
        $this->serializedEvent = $serializedEvent;
        $this->subscriberClass = $subscriberClass;
        $this->eventDispatchId = $eventDispatchId;
        $this->eventStoreId = $eventStoreId;
    }
    
    public function getEventClass(): string
    {
        return $this->eventClass;
    }
    
    public function getSerializedEvent(): string
    {
        return $this->serializedEvent;
    }
    
    public function getSubscriberClass(): string
    {
        return $this->subscriberClass;
    }
    
    public function getEventDispatchId(): int
    {
        return $this->eventDispatchId;
    }
    
    public function getEventStoreId(): int
    {
        return $this->eventStoreId;
    }
}
