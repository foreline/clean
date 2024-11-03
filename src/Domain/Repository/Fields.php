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
        $this->fields = $fields;
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