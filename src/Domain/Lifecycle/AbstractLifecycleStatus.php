<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

use Domain\Aggregate\AggregateInterface;
use Domain\Entity\AbstractEntity;

/**
 * Базовая реализация настраиваемого пользователем статуса жизненного цикла.
 *
 * Наследники объявляют типизированное свойство кода конкретным enum'ом
 * (например, PurchaseStatusEnum) и реализуют {@see getCode()} / setCode().
 * Свойства объявлены как `protected`, поскольку класс является базовым,
 * а не leaf-агрегатом — конечные агрегаты сохраняют инкапсуляцию через
 * публичный API getter/setter.
 */
abstract class AbstractLifecycleStatus extends AbstractEntity implements
    LifecycleStatusInterface,
    AggregateInterface
{
    /** @var string Описание */
    protected string $description = '';

    /** @var string Цвет */
    protected string $color = '';

    /** @var int Сортировка */
    protected int $sort = 1;

    /** @var bool Активность */
    protected bool $active = true;

    /** @var bool По умолчанию */
    protected bool $defaultStatus = false;


    /**
     * Системный код статуса.
     */
    abstract public function getCode(): LifecycleStatusEnumInterface;


    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }


    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @param string $color
     * @return static
     */
    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }


    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     * @return static
     */
    public function setSort(int $sort): static
    {
        $this->sort = $sort;
        return $this;
    }


    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return static
     */
    public function setActive(bool $active): static
    {
        $this->active = $active;
        return $this;
    }


    /**
     * @return bool
     */
    public function isDefaultStatus(): bool
    {
        return $this->defaultStatus;
    }

    /**
     * @param bool $defaultStatus
     * @return static
     */
    public function setDefaultStatus(bool $defaultStatus): static
    {
        $this->defaultStatus = $defaultStatus;
        return $this;
    }


    /**
     * Соответствует ли статус хотя бы одному из перечисленных системных кодов.
     */
    public function is(LifecycleStatusEnumInterface ...$codes): bool
    {
        $current = $this->getCode();
        foreach ( $codes as $code ) {
            if ( $current === $code ) {
                return true;
            }
        }
        return false;
    }


    /**
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->getCode()->isInProgress();
    }


    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return $this->getCode()->isFinal();
    }
}
