<?php
declare(strict_types=1);

namespace Domain\Lifecycle\Service;

use Domain\Lifecycle\LifecycleStatusEnumInterface;
use Domain\Lifecycle\StatusTransitionsInterface;

/**
 * Базовая реализация {@see StatusTransitionsInterface} с разумными
 * значениями по умолчанию для всех методов кроме
 * {@see self::canTransition()}, который должен быть реализован конкретным
 * сервисом ограниченного контекста (например, `PurchaseStatusTransitions`).
 *
 * Конкретные реализации обязаны:
 *
 * 1. Реализовать {@see self::canTransition()} (часть базового
 *    {@see \Domain\Lifecycle\StatusTransitionsInterface}).
 * 2. Реализовать {@see self::getEnumClass()} — возвращает FQCN enum-класса,
 *    используется матричными методами для итерации по `cases()`.
 *
 * Опционально могут переопределить {@see self::transitionDeniedReason()}
 * (для предметных формулировок) и {@see self::reasonRequiredFor()}
 * (если переход требует обязательного указания причины).
 *
 * Сахар-методы (`*Matrix()`) построены поверх остальных — переопределять их
 * как правило не нужно.
 */
abstract class AbstractLifecycleStatusTransitions implements StatusTransitionsInterface
{
    /**
     * FQCN enum-класса, реализующего {@see LifecycleStatusEnumInterface}.
     * Используется матричными методами для перечисления всех `cases()`.
     *
     * @return class-string<LifecycleStatusEnumInterface>
     */
    abstract protected function getEnumClass(): string;

    /**
     * @inheritDoc
     */
    public function allowedFrom(?LifecycleStatusEnumInterface $from): array
    {
        $allowed = [];
        foreach ( $this->getCases() as $case ) {
            if ( $this->canTransition($from, $case) ) {
                $allowed[] = $case;
            }
        }

        return $allowed;
    }

    /**
     * @inheritDoc
     */
    public function requiresApproval(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool {
        return false;
    }

    /**
     * Базовая реализация: для `null`-источника (несохранённая сущность)
     * допускает любой целевой статус. Для не-null источника делегирует
     * к {@see self::canTransition()}. Конкретные классы могут переопределить
     * для более информативных сообщений.
     *
     * @inheritDoc
     */
    public function transitionDeniedReason(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): ?string {
        if ( null === $from ) {
            return null;
        }

        if ( $this->canTransition($from, $to) ) {
            return null;
        }

        return sprintf('Переход «%s» → «%s» не разрешён.', $from->name, $to->name);
    }

    /**
     * @inheritDoc
     */
    public function reasonRequiredFor(
        ?LifecycleStatusEnumInterface $from,
        LifecycleStatusEnumInterface $to,
    ): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function canTransitionMatrix(): array
    {
        $matrix = [];
        $cases = $this->getCases();
        $froms = array_merge([null], $cases);

        foreach ( $froms as $from ) {
            $key = self::matrixKey($from);
            $matrix[$key] = [];
            foreach ( $cases as $to ) {
                if ( null === $from ) {
                    $matrix[$key][(string)$to->value] = null === $this->transitionDeniedReason(null, $to);
                } else {
                    $matrix[$key][(string)$to->value] = $this->canTransition($from, $to);
                }
            }
        }

        return $matrix;
    }

    /**
     * @inheritDoc
     */
    public function transitionDeniedReasonMatrix(): array
    {
        $matrix = [];
        $cases = $this->getCases();
        $froms = array_merge([null], $cases);

        foreach ( $froms as $from ) {
            $key = self::matrixKey($from);
            $matrix[$key] = [];
            foreach ( $cases as $to ) {
                $matrix[$key][(string)$to->value] = $this->transitionDeniedReason($from, $to);
            }
        }

        return $matrix;
    }

    /**
     * @inheritDoc
     */
    public function reasonRequiredMatrix(): array
    {
        $matrix = [];
        $cases = $this->getCases();
        $froms = array_merge([null], $cases);

        foreach ( $froms as $from ) {
            $key = self::matrixKey($from);
            $matrix[$key] = [];
            foreach ( $cases as $to ) {
                $matrix[$key][(string)$to->value] = $this->reasonRequiredFor($from, $to);
            }
        }

        return $matrix;
    }

    /**
     * @return LifecycleStatusEnumInterface[]
     */
    private function getCases(): array
    {
        $enumClass = $this->getEnumClass();
        /** @var class-string<\BackedEnum&LifecycleStatusEnumInterface> $enumClass */
        return $enumClass::cases();
    }

    /**
     * @param LifecycleStatusEnumInterface|null $case
     * @return string
     */
    private static function matrixKey(?LifecycleStatusEnumInterface $case): string
    {
        return null === $case ? 'null' : (string)$case->value;
    }
}
