<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

/**
 * Контракт матрицы переходов жизненного цикла.
 *
 * Реализуется на уровне ограниченного контекста отдельным сервисным классом
 * (например, PurchaseStatusTransitions). Матрица захардкожена в коде —
 * это сознательный выбор ради типобезопасности и производительности
 * (O(1) проверка перехода, без обращений к БД).
 */
interface StatusTransitionsInterface
{
    /**
     * Допустимые целевые статусы из заданного исходного.
     *
     * @return LifecycleStatusEnumInterface[]
     */
    public function allowedFrom(LifecycleStatusEnumInterface $from): array;

    /**
     * Разрешён ли переход из {@param $from} в {@param $to}.
     */
    public function canTransition(
        LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool;

    /**
     * Требует ли переход явного согласования (см. подсистему Approvals).
     */
    public function requiresApproval(
        LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool;
}
