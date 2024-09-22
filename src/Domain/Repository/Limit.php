<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
class Limit implements LimitInterface
{
    private int $limit = 0;
    private int $offset = 0;
    private int $pageNum = 0;
    
    /**
     * @param int $limit
     * @return self
     */
    public function setLimit(int $limit = 1): self
    {
        // 0 - no limit
        //Assert::greaterThan($limit, 0, 'Limit must be greater than 0');
        $this->limit = $limit;
        return $this;
    }
    
    /**
     * @param int $limit
     * @param int $offset
     * @param int $pageNum
     * @return self
     */
    public function set(int $limit = 1, int $offset = 0, int $pageNum = 1): self
    {
        //Assert::greaterThan($limit, 0, 'Limit must be greater than 0');
        $this->limit = $limit;
        $this->offset = $offset;
        $this->pageNum = $pageNum;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
    
    /**
     * @param int $offset
     * @return self
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }
    
    /**
     * @return int
     */
    public function getPageNum(): int
    {
        return $this->pageNum;
    }
    
    /**
     * @param int $pageNum
     * @return self
     */
    public function setPageNum(int $pageNum): self
    {
        //Assert::greaterThanEq($pageNum, 1, 'Page number must be greater than 0');
        $this->pageNum = $pageNum;
        return $this;
    }
    
    /**
     * @return array{limit: int, offset: int, pageNum: int}
     */
    public function getLimits(): array
    {
        return [
            'limit'     => $this->limit,
            'offset'    => $this->offset,
            'page_num'  => $this->pageNum,
        ];
    }
    
    /**
     * @return self
     */
    public function reset(): self
    {
        $this->limit = 0;
        $this->offset = 0;
        return $this;
    }
}