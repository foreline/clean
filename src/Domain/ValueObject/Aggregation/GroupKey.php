<?php

declare(strict_types=1);

namespace Domain\ValueObject\Aggregation;

use BackedEnum;
use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

/**
 * Базовый класс ключа группировки.
 *
 * Concrete subclasses define the grouping dimensions as typed properties.
 * The reflection-based getHash() generates a unique composite key automatically
 * from whatever properties the subclass declares.
 */
abstract /*readonly*/ class GroupKey
{
    /**
     * Генерирует уникальный хэш ключа группировки.
     *
     * Uses reflection to iterate all properties of the concrete subclass
     * and build a deterministic string key. Handles entities (getId()),
     * enums (value/name), scalars, and null.
     */
    public function getHash(): string
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(
            ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE
        );
        
        $parts = [];
        
        foreach ( $properties as $property ) {
            
            $value = $property->getValue($this);
            
            $parts[] = match (true) {
                null === $value
                => 'null',
                is_object($value) && method_exists($value, 'getId')
                => (string) $value->getId(),
                $value instanceof BackedEnum
                => (string) $value->value,
                $value instanceof UnitEnum
                => $value->name,
                is_scalar($value)
                => (string) $value,
                default
                => serialize($value),
            };
        }
        
        return implode(':', $parts);
    }
    
    /**
     * Сравнение ключей группировки.
     */
    public function equals(self $other): bool
    {
        return $this->getHash() === $other->getHash();
    }
}
