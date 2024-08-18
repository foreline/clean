<?php
declare(strict_types=1);

namespace Domain\Event;

use DateTimeImmutable;

/**
 * Хранимое событие
 */
class StoredEvent implements EventInterface
{
    private int $id;
    private string $eventBody;
    private DateTimeImmutable $occurredOn;
    private string $typeName;

    /**
     * @param string $typeName
     * @param DateTimeImmutable $occurredOn
     * @param string $eventBody
     */
    public function __construct(string $typeName, DateTimeImmutable $occurredOn, string $eventBody)
    {
        $this->eventBody = $eventBody;
        $this->typeName = $typeName;
        $this->occurredOn = $occurredOn;
    }

    /**
     * @return string
     */
    public function eventBody(): string
    {
        return $this->eventBody;
    }

    /**
     * @return int
     */
    public function eventId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function typeName(): string
    {
        return $this->typeName;
    }

    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}