<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

/**
 * Контракт системного кода статуса жизненного цикла сущности.
 *
 * Реализуется backed enum'ом каждого ограниченного контекста
 * (например, PurchaseStatusEnum, AssetStatusEnum, TicketStatusEnum).
 *
 * Системные коды неизменяемы: пользовательская кастомизация (название,
 * описание, цвет, порядок) хранится в агрегате {@see LifecycleStatusInterface}.
 */
interface LifecycleStatusEnumInterface
{
    /**
     * Отображаемое имя по умолчанию (RU).
     */
    public function name(): string;

    /**
     * Описание по умолчанию (RU).
     */
    public function description(): string;

    /**
     * Цвет по умолчанию (HEX, например `#3498db`).
     */
    public function defaultColor(): string;

    /**
     * Порядок сортировки по умолчанию.
     */
    public function defaultSort(): int;

    /**
     * Признак статуса «в процессе» (не финальный).
     */
    public function isInProgress(): bool;

    /**
     * Признак финального статуса (терминальный, переходы запрещены или ограничены).
     */
    public function isFinal(): bool;
}
