<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Событие, несущее предметную сущность.
 *
 * Помечает доменные события, из которых можно достать агрегат/сущность
 * (getEntity) без duck typing. Реализациям разрешается сужать тип
 * возвращаемого значения до конкретного агрегата (ковариантность).
 */
interface EntityAwareEventInterface
{
    /**
     * Предметная сущность события (агрегат).
     *
     * @return object
     */
    public function getEntity();
}
