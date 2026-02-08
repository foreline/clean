<?php
declare(strict_types=1);

namespace Infrastructure\Event\Message;

/**
 * Symfony Messenger message for async event processing.
 *
 * Carries the serialized event payload and target subscriber class name
 * through the message transport for deferred processing by the worker.
 */
final class AsyncEventMessage
{
    /** @var string Event FQCN */
    private string $eventClass;
    
    /** @var string Serialized event payload */
    private string $serializedEvent;
    
    /** @var string Subscriber FQCN */
    private string $subscriberClass;
    
    /** @var int Event store record ID */
    private int $eventStoreId;
    
    public function __construct(
        string $eventClass,
        string $serializedEvent,
        string $subscriberClass,
        int $eventStoreId,
    ) {
        $this->eventClass = $eventClass;
        $this->serializedEvent = $serializedEvent;
        $this->subscriberClass = $subscriberClass;
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
    
    public function getEventStoreId(): int
    {
        return $this->eventStoreId;
    }
}
