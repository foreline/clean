<?php
declare(strict_types=1);

namespace Domain\Lifecycle\Exception;

use DomainException;
use Domain\Lifecycle\LifecycleStatusEnumInterface;
use Throwable;

/**
 * Бросается, когда сервис изменения статуса пытается выполнить переход,
 * запрещённый матрицей {@see \Domain\Lifecycle\StatusTransitionsInterface}.
 */
class InvalidLifecycleTransition extends DomainException
{
    /** @var LifecycleStatusEnumInterface Исходный статус */
    private LifecycleStatusEnumInterface $fromStatus;

    /** @var LifecycleStatusEnumInterface Целевой статус */
    private LifecycleStatusEnumInterface $toStatus;


    public function __construct(
        LifecycleStatusEnumInterface $fromStatus,
        LifecycleStatusEnumInterface $toStatus,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $this->fromStatus = $fromStatus;
        $this->toStatus   = $toStatus;

        if ( '' === $message ) {
            $message = sprintf(
                'Недопустимый переход жизненного цикла: %s -> %s',
                $this->describe($fromStatus),
                $this->describe($toStatus),
            );
        }

        parent::__construct($message, $code, $previous);
    }


    /**
     * @return LifecycleStatusEnumInterface
     */
    public function getFromStatus(): LifecycleStatusEnumInterface
    {
        return $this->fromStatus;
    }


    /**
     * @return LifecycleStatusEnumInterface
     */
    public function getToStatus(): LifecycleStatusEnumInterface
    {
        return $this->toStatus;
    }


    /**
     * Текстовое представление кода статуса для сообщения исключения.
     */
    private function describe(LifecycleStatusEnumInterface $status): string
    {
        if ( $status instanceof \BackedEnum ) {
            return (string)$status->value;
        }
        if ( $status instanceof \UnitEnum ) {
            return $status->name;
        }
        return $status::class;
    }
}
