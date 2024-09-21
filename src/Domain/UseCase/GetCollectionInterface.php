<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Aggregate\IteratorInterface;

/**
 * Интерфейс для сервиса выборки коллекции сущностей
 */
interface GetCollectionInterface
{
    /**
     * @return ?IteratorInterface
     */
    public function get(): ?IteratorInterface;
    
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