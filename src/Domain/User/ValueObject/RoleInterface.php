<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

/**
 * Базовый интерфейс определения ролей.
 *
 * Определяет контракт для классов, описывающих
 * набор ролей в рамках ограниченного контекста.
 */
interface RoleInterface
{
    /**
     * Возвращает определения ролей: код → наименование и описание.
     *
     * @return array<string, array{name: string, description: string}>
     */
    public static function getRoleDefinitions(): array;
    
    /**
     * Возвращает идентификатор ограниченного контекста.
     *
     * @return string
     */
    public static function getContext(): string;
}
