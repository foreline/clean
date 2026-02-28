<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

/**
 * Интерфейс ролей управления сущностью.
 *
 * Определяет контракт для классов, описывающих
 * CRUD-роли конкретной сущности ограниченного контекста.
 */
interface EntityRoleInterface extends RoleInterface
{
    /**
     * Возвращает код роли менеджера сущности.
     *
     * @return string
     */
    public static function getEntityManagerCode(): string;
}
