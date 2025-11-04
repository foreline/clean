<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Limit class is designed to be extended for specific repository needs (add repository specific limit methods).
 * A limit is used to specify pagination criteria for querying a repository.
 * It can hold limit, offset and page number.
 */
class Limit implements LimitInterface, JsonSerializable
{
    private int $limit = 0;
    private int $offset = 0;
    private int $pageNum = 0;
    
    private ?ServiceInterface $service;
    
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
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
    
    /**
     * @return ServiceInterface|null
     */
    public function endLimit(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Specify limit data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array Data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    public function jsonSerialize(): array
    {
        return [
            'limit'     => $this->limit,
            'offset'    => $this->offset,
            'page_num'  => $this->pageNum,
            'version'   => '0.1',
            'metadata'  => [
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
    
    /**
     * Reconstruct limit from JSON string
     *
     * @param string $json
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromJson(string $json, ?ServiceInterface $service = null): static
    {
        $data = json_decode($json, true);
        
        if ( !is_array($data) ) {
            throw new InvalidArgumentException('Invalid JSON format for limit');
        }
        
        return static::fromArray($data, $service);
    }
    
    /**
     * Restore limit from array
     *
     * @param array $data
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromArray(array $data, ?ServiceInterface $service = null): static
    {
        $limit = new static($service);
        
        if ( isset($data['limit']) ) {
            $limit->setLimit((int) $data['limit']);
        }
        
        if ( isset($data['offset']) ) {
            $limit->setOffset((int) $data['offset']);
        }
        
        if ( isset($data['page_num']) ) {
            $limit->setPageNum((int) $data['page_num']);
        }
        
        return $limit;
    }
}