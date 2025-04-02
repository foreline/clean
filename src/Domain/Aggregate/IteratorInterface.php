<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use Domain\Entity\ToArrayInterface;
use Iterator;
use ReturnTypeWillChange;

/**
 * Flexible version of \Iterator Interface.
 * Entities Typed collections should implement this interface.
 */
interface IteratorInterface extends Iterator, ToArrayInterface
{
    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): mixed;

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return self
     */
    #[ReturnTypeWillChange]
    public function next(): self;

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed TKey on success, or null on failure.
     */
    public function key(): mixed;

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool;

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return self
     */
    #[ReturnTypeWillChange]
    public function rewind(): self;

    /**
     * Returns elements count
     * @return int
     */
    public function getCount(): int;

    /**
     * @return mixed
     */
    public function getCollection(): mixed;

    /**
     * Add element to collection
     * @param AggregateInterface $item
     * @return self
     */
    public function addItem(AggregateInterface $item): self;

    /**
     * Add elements to collection
     * @param IteratorInterface $items
     * @return $this
     */
    
    //public function addItems(IteratorInterface $items): self;

    /**
     * @param ?Iterator $items
     * @return $this
     */
    public function setItems(?Iterator $items): self;

}