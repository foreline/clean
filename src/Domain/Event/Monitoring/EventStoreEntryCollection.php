<?php
declare(strict_types=1);

namespace Domain\Event\Monitoring;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Typed collection of EventStoreEntry objects.
 *
 * @implements IteratorAggregate<int, EventStoreEntry>
 */
class EventStoreEntryCollection implements Countable, IteratorAggregate
{
    /** @var EventStoreEntry[] */
    private array $entries;
    
    public function __construct(EventStoreEntry ...$entries)
    {
        $this->entries = $entries;
    }
    
    public function count(): int
    {
        return count($this->entries);
    }
    
    /**
     * @return ArrayIterator<int, EventStoreEntry>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->entries);
    }
    
    public function isEmpty(): bool
    {
        return 0 === count($this->entries);
    }
    
    /**
     * @return EventStoreEntry[]
     */
    public function toArray(): array
    {
        return $this->entries;
    }
    
    public function first(): ?EventStoreEntry
    {
        return $this->entries[0] ?? null;
    }
}
