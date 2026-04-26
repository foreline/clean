<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

/**
 * Контракт сущности, обладающей жизненным циклом.
 *
 * Реализуется host-агрегатом (например, Purchase, Asset, Ticket).
 * Гарантирует единообразный доступ к текущему статусу из общего инфраструктурного
 * кода (аудит, бейджи, отчёты) без знания конкретного контекста.
 */
interface LifecycleAwareInterface
{
    /**
     * Текущий статус сущности (null, если ещё не назначен).
     */
    public function getStatus(): ?LifecycleStatusInterface;

    /**
     * Установить статус сущности.
     */
    public function setStatus(?LifecycleStatusInterface $status): static;
}
