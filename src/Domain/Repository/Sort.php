<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Sort class is designed to be extended for specific repository needs (add repository specific sort methods).
 * A sort is used to specify sorting criteria for querying a repository.
 * It can hold sorting criteria, each represented as a key-value pair.
 */
class Sort implements SortInterface, JsonSerializable
{
    private ?ServiceInterface $service;
    
    /** @var array<string,string>  */
    private array $sort = [];
    
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * @return self
     */
    public function byRand(): self
    {
        $this->sort = [
            'rand'  => 'asc',
        ];
        return $this;
    }

    /**
     * @param string $sort ['asc', 'desc']
     * @return self
     */
    public function byCnt(string $sort = 'asc'): self
    {
        $this->sort = ['cnt' => $sort];
        return $this;
    }

    /**
     * Добавляет поле для сортировки
     * @param string $field
     * @param string $order
     * @return self
     */
    public function by(string $field, string $order = 'asc'): self
    {
        $field = mb_strtolower($field);
        $order = mb_strtolower($order);
    
        if ( !in_array($field, $this->sort) ) {
            $this->sort[$field] = $order;
        }
    
        return $this;
    }

    /**
     * Устанавливает поле для сортировки (сбрасывая текущие значения)
     * @param string $field
     * @param string $order
     * @return self
     */
    public function setSortBy(string $field, string $order = 'asc'): self
    {
        $field = mb_strtolower($field);
        $order = mb_strtolower($order);
    
        $this->sort = [];
        if ( !in_array($field, $this->sort) ) {
            $this->sort[$field] = $order;
        }
    
        return $this;
    }

    /**
     * Добавляет поле для сортировки
     * @param string $field Поле сортировки
     * @param string $order Порядок сортировки asc|desc
     * @return self
     */
    public function add(string $field, string $order = 'asc'): self
    {
        $this->sort[$field] = $order;
        return $this;
    }

    /**
     * @return array<string,string>
     */
    public function get(): array
    {
        return $this->sort;
    }

    /**
     * @param array<string,string> $sort
     * @return self
     */
    public function set(array $sort): self
    {
        foreach ( $sort as $sortBy => $sortOrder )
        {
            $this->by((string)$sortBy, $sortOrder);
        }
        return $this;
    }

    /**
     * @return self
     */
    public function reset(): self
    {
        $this->sort = [];
        return $this;
    }
    
    /**
     * @return ServiceInterface|null
     */
    public function endSort(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Specify sort data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    public function jsonSerialize(): array
    {
        return [
            'sort'      => $this->get(),
            'version'   => '0.1',
            'metadata'  => [
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
    
    /**
     * Reconstruct sort from JSON string
     *
     * @param string $json
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromJson(string $json, ?ServiceInterface $service = null): static
    {
        $data = json_decode($json, true);
        
        if ( !is_array($data) ) {
            throw new InvalidArgumentException('Invalid JSON format for sort');
        }
        
        return static::fromArray($data, $service);
    }
    
    /**
     * Restore sort from array
     *
     * @param array $data
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromArray(array $data, ?ServiceInterface $service = null): static
    {
        $sort = new static($service);
        
        if ( isset($data['sort']) && is_array($data['sort']) ) {
            $sort->set($data['sort']);
        }
        
        return $sort;
    }
}