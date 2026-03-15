<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Group class is designed to be extended for specific repository needs (add repository specific group methods).
 * A group is used to specify grouping criteria for querying a repository.
 */
class Group implements GroupInterface, JsonSerializable
{
    /** @var string[] */
    private array $group = [];
    
    /** @var int Минимальное количество для фильтрации */
    private int $minCount = 0;
    
    /** @var int Максимальное количество для фильтрации */
    private int $maxCount = 0;
    
    private ?ServiceInterface $service;
    
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * @param array $group
     * @return Group
     */
    public function set(array $group): self
    {
        $this->group = $group;
        return $this;
    }
    
    /**
     * @param string $groupField
     * @return $this
     */
    public function add(string $groupField): self
    {
        $this->group[] = $groupField;
        return $this;
    }
    
    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->group;
    }
    
    /**
     * @return GroupInterface
     */
    public function reset(): GroupInterface
    {
        $this->group    = [];
        $this->minCount = 0;
        $this->maxCount = 0;
        return $this;
    }
    
    /**
     * Минимальное количество элементов в группе.
     *
     * @param int $minCount
     * @return self
     */
    public function setMinCount(int $minCount): self
    {
        $this->minCount = $minCount;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getMinCount(): int
    {
        return $this->minCount;
    }
    
    /**
     * Максимальное количество элементов в группе.
     *
     * @param int $maxCount
     * @return self
     */
    public function setMaxCount(int $maxCount): self
    {
        $this->maxCount = $maxCount;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getMaxCount(): int
    {
        return $this->maxCount;
    }
    
    /**
     * Проверяет, удовлетворяет ли количество условиям фильтрации.
     *
     * @param int $count
     * @return bool
     */
    public function isCountAccepted(int $count): bool
    {
        if ( 0 < $this->minCount && $this->minCount > $count ) {
            return false;
        }
        
        if ( 0 < $this->maxCount && $count > $this->maxCount ) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return ServiceInterface|null
     */
    public function endGroup(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Specify group data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array Data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    public function jsonSerialize(): array
    {
        return [
            'group'     => $this->get(),
            'minCount'  => $this->getMinCount(),
            'maxCount'  => $this->getMaxCount(),
            'version'   => '0.1',
            'metadata'  => [
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
    
    /**
     * Reconstruct group from JSON string
     *
     * @param string $json
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromJson(string $json, ?ServiceInterface $service = null): static
    {
        $data = json_decode($json, true);
        
        if ( !is_array($data) ) {
            throw new InvalidArgumentException('Invalid JSON format for group');
        }
        
        return static::fromArray($data, $service);
    }
    
    /**
     * Restore group from array
     *
     * @param array $data
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromArray(array $data, ?ServiceInterface $service = null): static
    {
        $group = new static($service);
        
        if ( isset($data['group']) ) {
            $group->set($data['group']);
        }
        
        if ( isset($data['minCount']) ) {
            $group->setMinCount((int) $data['minCount']);
        }
        
        if ( isset($data['maxCount']) ) {
            $group->setMaxCount((int) $data['maxCount']);
        }
        
        return $group;
    }
}