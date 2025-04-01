<?php
declare(strict_types=1);

namespace Domain\File\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\Aggregate\IteratorInterface;
use Domain\Aggregate\IteratorTrait;
use Iterator;

/**
 * File Collection
 */
class FileCollection implements IteratorInterface
{
    use IteratorTrait;
    
    /** @var ?File[]  */
    private ?array $items;
    
    /**
     *
     */
    public function __construct()
    {
        $this->position = 0;
        $this->items = [];
    }
    
    /**
     * @return ?File[]
     */
    public function getCollection(): ?array
    {
        return $this->items;
    }
    
    /**
     * @param ?FileCollection $items
     * @return FileCollection
     */
    public function setItems(?Iterator $items): FileCollection
    {
        $this->items = [];
        
        foreach ( $items as $file ) {
            $this->addItem($file);
        }
        return $this;
    }
    
    /**
     * @param File|AggregateInterface $file
     * @return $this
     */
    public function addItem(File|AggregateInterface $file): self
    {
        if ( !$this->contains($file) ) {
            $this->items[] = $file;
        }
        
        return $this;
    }
    
    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return ?File
     */
    public function current(): ?File
    {
        return $this->valid() ? $this->items[$this->position] : null;
    }
    
    /**
     * @param bool $withUrl
     * @param string $delimiter
     * @return string
     */
    public function toString(bool $withUrl = true, string $delimiter = ', '): string
    {
        if ( !$this->valid() ) {
            return '';
        }
        
        return implode(
            $delimiter,
            array_map(
                fn (AggregateInterface $item): string => $withUrl ? $item->getDownloadLink() : $item->getName()
                ,$this->items
            )
        );
    }
    
    /**
     * @param FileInterface $file
     * @return bool
     */
    public function contains(FileInterface $file): bool
    {
        foreach ( $this->getCollection() as $collectionItem ) {
            if ( $collectionItem->getId() === $file->getId() ) {
                return true;
            }
        }
        return false;
    }
}