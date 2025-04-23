<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 *
 */
class Fields implements FieldsInterface
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
     * Задает поля для выборки
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
     * Сбрасывает выбираемые поля
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
    
    
}