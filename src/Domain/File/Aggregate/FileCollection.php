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
            $this->items = null;
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
            $this->items = null;
            
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
            if ( null === $this->items ) {
                $this->items = [];
            }
            $this->items[] = $file;
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
        
    }