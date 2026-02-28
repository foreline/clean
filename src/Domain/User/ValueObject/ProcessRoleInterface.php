<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

/**
 * Интерфейс процессных ролей.
 *
 * Определяет контракт для классов, описывающих
 * процессные роли ограниченного контекста и их иерархию
 * наследования от ролей управления сущностями.
 */
interface ProcessRoleInterface extends RoleInterface
{
    /**
     * Возвращает иерархию процессных ролей.
     *
     * Карта наследования: процессная роль - массив наследуемых ролей сущностей.
     *
     * @return array<string, string[]>
     */
    public static function getHierarchy(): array;
    
    /**
     * Возвращает отображаемое наименование контекста на русском языке.
     *
     * @return string
     */
    public static function getContextDisplayName(): string;
}
