<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Fields class is designed to be extended for specific repository needs (add repository specific fields methods).
 * Fields are used to specify which fields to select when querying a repository.
 */
class Fields implements FieldsInterface, JsonSerializable
{
    /** @var string[] */
    private array $fields = [];
    
    private ?ServiceInterface $service;
    
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * Sets the fields to select
     *
     * @param string[] $fields
     * @return self
     */
    public function set(array $fields = []): self
    {
        $this->fields = array_unique($fields);
        return $this;
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->fields;
    }
    
    /**
     * @param string $field
     * @return $this
     */
    public function add(string $field): self
    {
        if ( !in_array($field, $this->fields, true) ) {
            $this->fields[] = $field;
        }
        return $this;
    }
    
    /**
     * @param string $field
     * @return $this
     */
    public function remove(string $field): self
    {
        if ( in_array($field, $this->fields, true) ) {
            unset($this->fields[array_search($field, $this->fields, true)]);
        }
        return $this;
    }

    /**
     * Resets the fields list
     * @return self
     */
    public function reset(): self
    {
        $this->fields = [];
        return $this;
    }
    
    /**
     * @return ServiceInterface|null
     */
    public function endFields(): ?ServiceInterface
    {
        return $this->service;
    }
    
    /**
     * Specify fields data which should be serialized to JSON
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array Data which can be serialized by json_encode, which is a value of any type other than a resource.
     */
    public function jsonSerialize(): array
    {
        return [
            'fields'    => $this->get(),
            'version'   => '0.1',
            'metadata'  => [
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }
    
    /**
     * Reconstruct fields from JSON string
     *
     * @param string $json
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromJson(string $json, ?ServiceInterface $service = null): static
    {
        $data = json_decode($json, true);
        
        if ( !is_array($data) ) {
            throw new InvalidArgumentException('Invalid JSON format for fields');
        }
        
        return static::fromArray($data, $service);
    }
    
    /**
     * Restore fields from array
     *
     * @param array $data
     * @param ServiceInterface|null $service
     * @return static
     */
    public static function fromArray(array $data, ?ServiceInterface $service = null): static
    {
        $fields = new static($service);
        
        if ( isset($data['fields']) ) {
            $fields->set($data['fields']);
        }
        
        return $fields;
    }
}