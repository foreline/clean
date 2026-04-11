<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

use Domain\ValueObject\StringValueObjectInterface;
use Domain\ValueObject\EnumValueObjectInterface;
use InvalidArgumentException;
use ReflectionClass;

/**
 * Enhanced Role design with better abstraction and flexibility
 */
abstract class AbstractRole implements StringValueObjectInterface, EnumValueObjectInterface
{
    protected string $code;
    protected static array $roleHierarchy = [];
    protected static array $roleNames = [];
    
    public function __construct(string $code)
    {
        $this->code = $code;
        $this->validateRole($code);
    }
    
    /**
     * Define role hierarchy in child classes
     * Should return array in format: ['parent_role' => ['child1', 'child2']]
     */
    abstract protected static function defineHierarchy(): array;
    
    /**
     * Define role names in child classes
     */
    abstract protected static function defineNames(): array;
    
    /**
     * Get all roles this role can perform (including inherited)
     */
    public function getEffectiveRoles(): array
    {
        $effective = [$this->code];
        $hierarchy = static::getHierarchy();
        
        if (isset($hierarchy[$this->code])) {
            foreach ($hierarchy[$this->code] as $inheritedRole) {
                $roleInstance = new static($inheritedRole);
                $effective = array_merge($effective, $roleInstance->getEffectiveRoles());
            }
        }
        
        return array_unique($effective);
    }
    
    /**
     * Check if this role can perform another role's actions
     */
    public function can(string $action): bool
    {
        return in_array($action, $this->getEffectiveRoles(), true);
    }
    
    /**
     * Get role hierarchy (cached)
     */
    protected static function getHierarchy(): array
    {
        $class = static::class;
        if ( !isset(static::$roleHierarchy[$class]) ) {
            static::$roleHierarchy[$class] = static::defineHierarchy();
        }
        return static::$roleHierarchy[$class];
    }
    
    /**
     * Validate if role code is defined in this role class
     */
    protected function validateRole(string $code): void
    {
        $reflection = new ReflectionClass(static::class);
        $constants = $reflection->getConstants();
        
        if ( !in_array($code, $constants, true) ) {
            throw new InvalidArgumentException("Role '{$code}' is not defined in " . static::class);
        }
    }
    
    public function getRole(): string
    {
        return $this->code;
    }
    
    public function getName(): string
    {
        $names = static::defineNames();
        return $names[$this->code] ?? $this->code;
    }
    
    public function __toString(): string
    {
        return $this->code;
    }
}
