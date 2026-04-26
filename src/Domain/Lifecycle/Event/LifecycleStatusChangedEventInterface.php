<?php
declare(strict_types=1);

namespace Domain\Lifecycle\Event;

use Domain\Event\EventInterface;
use Domain\Lifecycle\LifecycleAwareInterface;
use Domain\Lifecycle\LifecycleStatusInterface;
use Domain\User\Aggregate\UserInterface;

/**
 * Маркер-контракт события смены статуса жизненного цикла.
 *
 * Реализуется конкретным <Entity>StatusChangedEvent каждого контекста
 * (генерируется CRUD-генератором). Позволяет общим подписчикам (аудит,
 * бейджи, отчёты) реагировать на смену статуса любой сущности без
 * знания конкретного типа.
 *
 * Свободно-текстовый {@see getReason()} (а не enum-код) — сознательный выбор;
 * при необходимости контекст-специфичный enum причин может быть добавлен
 * как непрерывное расширение.
 *
 * Для нового объекта, создаваемого со статусом по умолчанию,
 * {@see getFromStatus()} возвращает `null` — аудит-подписчики могут
 * полагаться на это и не обрабатывать инициализацию как особый случай.
 */
interface LifecycleStatusChangedEventInterface extends EventInterface
{
    /**
     * Сущность, чей статус изменился.
     */
    public function getEntity(): LifecycleAwareInterface;

    /**
     * Предыдущий статус. `null` — событие первичного назначения статуса
     * при создании сущности.
     */
    public function getFromStatus(): ?LifecycleStatusInterface;

    /**
     * Новый статус.
     */
    public function getToStatus(): LifecycleStatusInterface;

    /**
     * Свободно-текстовая причина изменения. Может быть пустой строкой.
     */
    public function getReason(): string;

    /**
     * Инициатор изменения. `null` — системное / автоматическое изменение.
     */
    public function getInitiator(): ?UserInterface;
}
