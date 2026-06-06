<?php
declare(strict_types=1);

namespace Domain\Event;

use Webmozart\Assert\Assert;

/**
 * Immutable snapshot of a pending debounced invocation.
 *
 * A state record represents one debounce bucket (identified by {@see getKey()})
 * that is waiting to be flushed. It carries everything required to invoke the
 * target subscriber later: the subscriber class, the serialized triggering
 * event, and the timing metadata used to decide when the invocation is due.
 *
 * Instances are persisted by {@see DebounceStorageInterface} implementations.
 */
final class DebounceState
{
    /** @var string Namespaced debounce bucket key. */
    private string $key;

    /** @var class-string<SubscriberInterface> Target subscriber FQCN. */
    private string $subscriberClass;

    /** @var class-string<EventInterface> Triggering event FQCN. */
    private string $eventClass;

    /** @var string Serialized triggering event payload. */
    private string $serializedEvent;

    /** @var int Epoch in milliseconds when the invocation becomes due. */
    private int $dueAtMs;

    /** @var int Epoch in milliseconds of the first event in this window. */
    private int $firstSeenAtMs;

    /** @var int Epoch in milliseconds hard deadline (0 = no cap). */
    private int $deadlineMs;

    /**
     * @param string $key
     * @param class-string<SubscriberInterface> $subscriberClass
     * @param class-string<EventInterface> $eventClass
     * @param string $serializedEvent
     * @param int $dueAtMs
     * @param int $firstSeenAtMs
     * @param int $deadlineMs
     */
    public function __construct(
        string $key,
        string $subscriberClass,
        string $eventClass,
        string $serializedEvent,
        int $dueAtMs,
        int $firstSeenAtMs,
        int $deadlineMs,
    ) {
        Assert::stringNotEmpty($key);
        Assert::stringNotEmpty($subscriberClass);
        Assert::stringNotEmpty($eventClass);

        $this->key = $key;
        $this->subscriberClass = $subscriberClass;
        $this->eventClass = $eventClass;
        $this->serializedEvent = $serializedEvent;
        $this->dueAtMs = $dueAtMs;
        $this->firstSeenAtMs = $firstSeenAtMs;
        $this->deadlineMs = $deadlineMs;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return class-string<SubscriberInterface>
     */
    public function getSubscriberClass(): string
    {
        return $this->subscriberClass;
    }

    /**
     * @return class-string<EventInterface>
     */
    public function getEventClass(): string
    {
        return $this->eventClass;
    }

    /**
     * @return string
     */
    public function getSerializedEvent(): string
    {
        return $this->serializedEvent;
    }

    /**
     * @return int
     */
    public function getDueAtMs(): int
    {
        return $this->dueAtMs;
    }

    /**
     * @return int
     */
    public function getFirstSeenAtMs(): int
    {
        return $this->firstSeenAtMs;
    }

    /**
     * @return int
     */
    public function getDeadlineMs(): int
    {
        return $this->deadlineMs;
    }

    /**
     * Serializes the state into a plain array for storage.
     *
     * @return array{key: string, subscriberClass: string, eventClass: string, serializedEvent: string, dueAtMs: int, firstSeenAtMs: int, deadlineMs: int}
     */
    public function toArray(): array
    {
        return [
            'key'             => $this->key,
            'subscriberClass' => $this->subscriberClass,
            'eventClass'      => $this->eventClass,
            'serializedEvent' => $this->serializedEvent,
            'dueAtMs'         => $this->dueAtMs,
            'firstSeenAtMs'   => $this->firstSeenAtMs,
            'deadlineMs'      => $this->deadlineMs,
        ];
    }

    /**
     * Reconstructs a state from its array representation.
     *
     * @param array<string, mixed> $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'key');
        Assert::keyExists($data, 'subscriberClass');
        Assert::keyExists($data, 'eventClass');
        Assert::keyExists($data, 'serializedEvent');
        Assert::keyExists($data, 'dueAtMs');
        Assert::keyExists($data, 'firstSeenAtMs');
        Assert::keyExists($data, 'deadlineMs');

        /** @var class-string<SubscriberInterface> $subscriberClass */
        $subscriberClass = (string)$data['subscriberClass'];
        /** @var class-string<EventInterface> $eventClass */
        $eventClass = (string)$data['eventClass'];

        return new self(
            (string)$data['key'],
            $subscriberClass,
            $eventClass,
            (string)$data['serializedEvent'],
            (int)$data['dueAtMs'],
            (int)$data['firstSeenAtMs'],
            (int)$data['deadlineMs'],
        );
    }
}
