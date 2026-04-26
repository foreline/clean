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
 *
 * Все методы принимают **nullable** `$from` — это легитимный случай
 * «несохранённая сущность переходит в свой первый статус».
 *
 * Конкретные реализации удобно наследовать от
 * {@see \Domain\Lifecycle\Service\AbstractLifecycleStatusTransitions},
 * который содержит работоспособные реализации UI-методов поверх единственного
 * required-метода {@see self::canTransition()}.
 */
interface StatusTransitionsInterface
{
    /**
     * Допустимые целевые статусы из заданного исходного.
     *
     * @return LifecycleStatusEnumInterface[]
     */
    public function allowedFrom(?LifecycleStatusEnumInterface $from): array;

    /**
     * Разрешён ли переход из {@param $from} в {@param $to}.
     *
     * @param LifecycleStatusEnumInterface|null $from null означает «несохранённая сущность».
     * @param LifecycleStatusEnumInterface $to
     * @return bool
     */
    public function canTransition(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool;

    /**
     * Требует ли переход явного согласования (см. подсистему Approvals).
     *
     * @param LifecycleStatusEnumInterface|null $from
     * @param LifecycleStatusEnumInterface $to
     * @return bool
     */
    public function requiresApproval(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool;

    /**
     * Человеко-читаемая (русская) причина запрета перехода.
     *
     * Используется UI-слоем как `title` атрибут у `<option disabled>`
     * в `<select>`-элементах формы и для всплывающих подсказок.
     *
     * @param LifecycleStatusEnumInterface|null $from null означает «несохранённая сущность».
     * @param LifecycleStatusEnumInterface $to
     * @return string|null null, если переход разрешён.
     */
    public function transitionDeniedReason(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): ?string;

    /**
     * Требуется ли указать причину при выполнении перехода.
     *
     * Если `true`, UI обязан показать модальное окно с обязательным
     * textarea перед отправкой запроса на смену статуса.
     *
     * @param LifecycleStatusEnumInterface|null $from
     * @param LifecycleStatusEnumInterface $to
     * @return bool
     */
    public function reasonRequiredFor(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool;

    /**
     * Полная матрица допустимости переходов.
     *
     * Ключи внешнего массива — `value` enum-кейса источника либо строка `"null"`
     * для «несохранённой сущности». Ключи вложенного массива — `value` enum-кейса
     * назначения. Значения — `bool`.
     *
     * @return array<string, array<string, bool>>
     */
    public function canTransitionMatrix(): array;

    /**
     * Полная матрица человеко-читаемых причин запрета. Структура та же, что и
     * у {@see self::canTransitionMatrix()}, значения — `string|null` (null означает
     * "переход разрешён").
     *
     * @return array<string, array<string, string|null>>
     */
    public function transitionDeniedReasonMatrix(): array;

    /**
     * Полная матрица «требуется указать причину». Структура та же,
     * значения — `bool`.
     *
     * @return array<string, array<string, bool>>
     */
    public function reasonRequiredMatrix(): array;
}
