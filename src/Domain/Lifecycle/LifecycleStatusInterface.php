<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

/**
 * Контракт настраиваемого пользователем статуса жизненного цикла.
 *
 * Реализуется конкретными агрегатами статуса (например, PurchaseStatus,
 * AssetStatus). Системный код статуса возвращается через {@see getCode()}
 * и задаёт поведение классификации (in-progress / final).
 */
interface LifecycleStatusInterface
{
    /**
     * Системный код статуса.
     */
    public function getCode(): LifecycleStatusEnumInterface;

    /**
     * Пользовательское название.
     */
    public function getName(): string;

    /**
     * Пользовательское описание.
     */
    public function getDescription(): string;

    /**
     * Цвет (HEX).
     */
    public function getColor(): string;

    /**
     * Порядок сортировки.
     */
    public function getSort(): int;

    /**
     * Активность статуса (отключённые статусы скрываются в UI и недоступны для перехода).
     */
    public function isActive(): bool;

    /**
     * Является ли статус значением по умолчанию для новых сущностей.
     */
    public function isDefaultStatus(): bool;

    /**
     * Соответствует ли статус хотя бы одному из перечисленных системных кодов.
     */
    public function is(LifecycleStatusEnumInterface ...$codes): bool;

    /**
     * Делегирует в системный код: статус «в процессе».
     */
    public function isInProgress(): bool;

    /**
     * Делегирует в системный код: финальный статус.
     */
    public function isFinal(): bool;
}
