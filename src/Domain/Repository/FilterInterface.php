<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

/**
 * Интерфейс фильтра репозитория
 */
interface FilterInterface
{
    public function __construct(?ServiceInterface $service = null);
    
    /**
     * Сбрасывает фильтр. Выполняется в конце запроса.
     * @return $this
     */
    public function reset(): static;
    
    /**
     * @return ServiceInterface|null
     */
    public function endFilter(): ?ServiceInterface;
    
    /**
     * Задает фильтр по условию
     * @return ConditionFilterInterface|null
     */
    public function byCondition(): ?ConditionFilterInterface;
}