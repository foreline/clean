<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\User\Infrastructure\Repository\UserLimit;

/**
 * 
 */
trait UserLimitTrait
{
    public UserLimit $limit;
    
    /**
     * @param array{limit: int, offset: int, pageNum: int} $limit
     * @return $this
     */
    public function limits(array $limit): static
    {
        $this->limit->set((int)$limit['limit'], (int)$limit['offset'], (int)$limit['pageNum']);
        return $this;
    }
    
    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): static
    {
        $this->limit->setLimit($limit);
        return $this;
    }
    
    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): static
    {
        $this->limit->setOffset($offset);
        return $this;
    }
    
    /**
     * @param int $pageNum
     * @return $this
     */
    public function pageNum(int $pageNum): static
    {
        $this->limit->setPageNum($pageNum);
        return $this;
    }
}