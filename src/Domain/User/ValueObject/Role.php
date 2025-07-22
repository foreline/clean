<?php
declare(strict_types=1);

namespace Domain\User\ValueObject;

use Domain\ValueObject\StringValueObjectInterface;

/**
 * Роль пользователя
 */
class Role implements StringValueObjectInterface
{
    public const ADMIN = 'admin';
    
    private string $role;
    
    private array $names = [
        self::ADMIN     => 'Администратор',
    ];
    
    /**
     * @param string $role
     */
    public function __construct(string $role = '')
    {
        $this->role = $role;
    }
    
    /**
     * Get role inheritance hierarchy
     * Override this method in child classes to define role inheritance
     * 
     * @return array Array of role inheritance mappings [parent_role => [child_roles]]
     */
    protected function getInheritedRoles(): array
    {
        return [];
    }
    
    /**
     * Get all roles that this role inherits (including itself)
     * 
     * @return array
     */
    public function getAllInheritedRoles(): array
    {
        $allRoles = [$this->role];
        $inheritance = $this->getInheritedRoles();
        
        if (isset($inheritance[$this->role])) {
            foreach ($inheritance[$this->role] as $inheritedRole) {
                $roleInstance = new static($inheritedRole);
                $allRoles = array_merge($allRoles, $roleInstance->getAllInheritedRoles());
            }
        }
        
        return array_unique($allRoles);
    }
    
    /**
     * Check if this role has (or inherits) a specific role
     * 
     * @param string $roleCode
     * @return bool
     */
    public function hasRole(string $roleCode): bool
    {
        return in_array($roleCode, $this->getAllInheritedRoles(), true);
    }
    
    /**
     * Check if this role has any of the specified roles (or inherits them)
     * 
     * @param array $roleCodes
     * @return bool
     */
    public function hasAnyRole(array $roleCodes): bool
    {
        $inheritedRoles = $this->getAllInheritedRoles();
        return !empty(array_intersect($roleCodes, $inheritedRoles));
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->names[$this->role] ?? $this->role;
    }
    
    /**
     * @return $this
     */
    public function admin(): self
    {
        return new self(self::ADMIN);
    }
    
    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->equals(self::admin());
    }
    
    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }
    
    /**
     * @param Role $role
     * @return bool
     */
    public function equals(self $role): bool
    {
        return $this->role === $role->role;
    }
    
    /**
     * @param string $roleCode
     * @return bool
     */
    public function is(string $roleCode): bool
    {
        return $this->getRole() === $roleCode;
    }
    
    /**
     * @return array
     */
    public static function getAll(): array
    {
        return [
            (new self(self::ADMIN)),
        ];
    }
    
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getRole();
    }
}