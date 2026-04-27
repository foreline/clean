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
     *
     * @return LifecycleStatusEnumInterface
     */
    public function getCode(): LifecycleStatusEnumInterface;

    /**
     * Пользовательское название.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Пользовательское описание.
     *
     * @return string
     */
    public function getDescription(): string;
    
    /**
     * Подсказка для пользователя (например, при наведении мыши на статус в UI).
     * @return string
     */
    public function getHint(): string;

    /**
     * Цвет (HEX).
     *
     * @return string
     */
    public function getColor(): string;

    /**
     * Порядок сортировки.
     *
     * @return int
     */
    public function getSort(): int;

    /**
     * Активность статуса (отключённые статусы скрываются в UI и недоступны для перехода).
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Является ли статус значением по умолчанию для новых сущностей.
     *
     * @return bool
     */
    public function isDefaultStatus(): bool;

    /**
     * Соответствует ли статус хотя бы одному из перечисленных системных кодов.
     *
     * @param LifecycleStatusEnumInterface ...$codes
     * @return bool
     */
    public function is(LifecycleStatusEnumInterface ...$codes): bool;

    /**
     * Делегирует в системный код: статус «в процессе».
     *
     * @return bool
     */
    public function isInProgress(): bool;

    /**
     * Делегирует в системный код: финальный статус.
     *
     * @return bool
     */
    public function isFinal(): bool;
}
