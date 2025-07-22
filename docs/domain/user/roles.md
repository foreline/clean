# Enhanced Role System Documentation

## Overview

The enhanced Role system provides hierarchical role inheritance and namespace-specific role support. This allows for flexible permission management where higher-level roles automatically inherit permissions from lower-level roles.

## Key Features

### 1. Role Inheritance
Roles can inherit permissions from other roles through a hierarchical structure. For example:
- `AUTHOR` inherits from `COMMENTER` and `REVIEWER`
- `COMMENTER` inherits from `REVIEWER`
- `REVIEWER` has only its own permissions

### 2. Namespace-Specific Roles
Roles can be defined in domain-specific namespaces, allowing different modules to have their own role hierarchies.

### 3. Enhanced User Role Checking
The `User::in()` method now supports:
- Direct role checking
- Inherited role checking
- Role objects, constants, and strings
- Variadic parameters for multiple role checks

## Usage Examples

### Basic Role Definition

```php
use Domain\User\ValueObject\Role;

// Create a basic role
$adminRole = new Role('admin');
$userRole = new Role('user');
```

### Domain-Specific Role with Inheritance

```php
use Domain\User\ValueObject\Role;

class BlogPostRole extends Role
{
    public const REVIEWER = 'reviewer';
    public const COMMENTER = 'commenter';
    public const AUTHOR = 'author';
    
    protected function getInheritedRoles(): array
    {
        return [
            self::AUTHOR => [self::COMMENTER, self::REVIEWER],
            self::COMMENTER => [self::REVIEWER]
        ];
    }
}
```

### User Role Assignment and Checking

```php
use Domain\User\Aggregate\User;
use Domain\User\ValueObject\RoleCollection;

// Create user and assign roles
$user = new User();
$roleCollection = new RoleCollection();
$roleCollection->addItem(new BlogPostRole(BlogPostRole::AUTHOR));
$user->setRoles($roleCollection);

// Check roles (all will return true due to inheritance)
$user->in(BlogPostRole::AUTHOR);    // true
$user->in(BlogPostRole::COMMENTER); // true (inherited)
$user->in(BlogPostRole::REVIEWER);  // true (inherited)

// Multiple role checking
$user->in(BlogPostRole::REVIEWER, 'some_other_role'); // true (first role matches)

// Using Role objects
$reviewerRole = new BlogPostRole(BlogPostRole::REVIEWER);
$user->in($reviewerRole); // true (inherited)
```

## Role Inheritance Hierarchy

### BlogPostRole Example
```
AUTHOR
├── COMMENTER (inherited)
│   └── REVIEWER (inherited)
└── REVIEWER (inherited)
```

This means:
- An `AUTHOR` can perform all actions that a `COMMENTER` and `REVIEWER` can perform
- A `COMMENTER` can perform all actions that a `REVIEWER` can perform
- A `REVIEWER` can only perform `REVIEWER` actions

## API Reference

### Role Class Methods

#### `getAllInheritedRoles(): array`
Returns all roles that this role inherits, including itself.

#### `hasRole(string $roleCode): bool`
Checks if this role has (or inherits) a specific role.

#### `hasAnyRole(array $roleCodes): bool`
Checks if this role has any of the specified roles (including inherited).

### User Class Methods

#### `in(string|array|Role ...$rolesCode): bool`
Enhanced method that checks if the user has any of the specified roles, supporting:
- Role inheritance
- Multiple parameter types (string, Role object)
- Variadic parameters for multiple role checking

## Best Practices

### 1. Role Naming Convention
- Use UPPER_CASE constants for role codes
- Use descriptive names that reflect the role's purpose
- Keep role codes consistent with Group codes when applicable

### 2. Inheritance Design
- Design inheritance hierarchies logically (broader permissions inherit more specific ones)
- Avoid circular inheritance
- Keep inheritance depth reasonable (2-3 levels maximum)

### 3. Testing
- Test all inheritance paths
- Test role checking with different parameter types
- Test edge cases (empty roles, non-existent roles)

## Migration from Legacy System

The new system maintains backward compatibility:

```php
// Old way (still supported)
$user->in('admin');

// New way with inheritance
$user->in(BlogPostRole::AUTHOR); // Also matches COMMENTER and REVIEWER
```

## Performance Considerations

- Role inheritance is calculated on-demand
- Inheritance chains are cached within the Role object
- Avoid deep inheritance hierarchies (max 3-4 levels)
- Consider using Role registries for complex domain applications

## Examples and Demonstrations

See the [examples directory](./examples/) for practical demonstrations:

- **[Role System Demo](./examples/role_system_demo.php)**: Complete demonstration of role inheritance and checking
- **[Group-Role Integration](./examples/group_role_integration_demo.php)**: Shows integration with existing Group systems like Bitrix

## Related Documentation

- [Role Design Critique & Analysis](./role-design-critique.md)
- [Domain Layer Overview](../index.md)
