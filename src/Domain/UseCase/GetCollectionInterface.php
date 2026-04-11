<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Aggregate\CollectionInterface;
use Domain\Aggregate\IteratorInterface;

/**
 * Interface for the service of selecting a collection of entities
 */
interface GetCollectionInterface
{
    /**
     * @return IteratorInterface|CollectionInterface|null
     */
    public function get(): null|IteratorInterface|CollectionInterface;
    
    /**
     * Filters based on the provided criteria.
     * @param array<string,mixed> $filter
     * @return $this
     */
    public function filter(array $filter): self;
    
    /**
     * @param array<string,string> $sort
     * @return $this
     */
    public function sort(array $sort): self;
    
    /**
     * @param string[] $fields
     * @return $this
     */
    public function fields(array $fields): self;
    
    /**
     * @param array{limit: int, offset: int, pageNum: int} $limits
     * @return $this
     */
    public function limits(array $limits): self;
    
    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self;
    
    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self;
    
    /**
     * @param int $pageNum
     * @return $this
     */
    public function pageNum(int $pageNum): self;
}