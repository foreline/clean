<?php
declare(strict_types=1);

namespace Domain\Event;

/** Проверка интереса до сериализации; не заменяет проверку при обработке. */
interface AsyncEnqueueFilterInterface
{
    /** Проверка не должна изменять бизнес-данные. */
    public function shouldEnqueue(EventInterface $event): bool;
}
