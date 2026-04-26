<?php
declare(strict_types=1);

namespace Domain\Lifecycle;

/**
 * Расширенный контракт enum'а статуса жизненного цикла, объявляющий
 * системный статус по умолчанию и наборы статусов «в процессе» / финальных.
 *
 * Выделен в отдельный интерфейс, чтобы базовый
 * {@see LifecycleStatusEnumInterface} оставался пригодным для использования
 * как тип параметра / возврата без необходимости объявлять статические члены
 * на enum'е, который реализует только базовый контракт.
 */
interface LifecycleStatusEnumDefaultsInterface extends LifecycleStatusEnumInterface
{
    /**
     * Системный статус по умолчанию для новой сущности.
     */
    public static function default(): self;

    /**
     * Все статусы «в процессе».
     *
     * @return self[]
     */
    public static function getInProgressStatuses(): array;

    /**
     * Все финальные статусы.
     *
     * @return self[]
     */
    public static function getFinalStatuses(): array;
}
