<?php
declare(strict_types=1);

namespace Domain\Event;

use Domain\Event\Aggregate\EventCollection;

/**
 *
 */
trait RaiseEventTrait {

    protected array $events = [];

    /**
     * @return EventCollection
     */
    public function flush(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    /**
     * @param $event
     */
    protected function raise($event): void
    {
        $this->events[] = $event;
    }
}