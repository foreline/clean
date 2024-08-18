<?php
declare(strict_types=1);

namespace Domain\UseCase;

/**
 *
 */
class Fields
{
    /** @var string[] */
    private array $fields = [];

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
}