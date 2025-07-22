# User Domain Documentation

This section contains documentation for the User domain components, including the enhanced Role system.

## Core Components

### Role System
The enhanced role system provides hierarchical role inheritance and flexible permission management.

**Main Documentation**:
- **[Role System Guide](./roles.md)** - Complete guide to using the role system
- **[Design Analysis](./role-design-critique.md)** - Critical analysis and design recommendations

**Practical Examples**:
- **[Examples Directory](./examples/)** - Working code examples and demonstrations

### Key Features

#### ✅ **Role Inheritance**
Roles can inherit permissions from other roles:
```php
// AUTHOR automatically inherits COMMENTER and REVIEWER permissions
$user->in(BlogPostRole::AUTHOR); // Also grants commenter and reviewer access
```

#### ✅ **Domain-Specific Roles**
Each module can define its own role hierarchy:
```php
namespace App\Blog\Post;
class Role extends \Domain\User\Role {
    public const REVIEWER = 'reviewer';
    public const COMMENTER = 'commenter';
    public const AUTHOR = 'author';
}
```

#### ✅ **Group Integration**
Works seamlessly with existing database Groups (e.g., Bitrix CMS):
- Groups remain in database for persistence
- Roles provide business logic and inheritance
- Automatic mapping between Groups and Roles

## Quick Start

### 1. Define Domain Roles
```php
use Domain\User\ValueObject\Role;

class BlogPostRole extends Role {
    public const REVIEWER = 'reviewer';
    public const COMMENTER = 'commenter';
    public const AUTHOR = 'author';
    
    protected function getInheritedRoles(): array {
        return [
            self::AUTHOR => [self::COMMENTER, self::REVIEWER],
            self::COMMENTER => [self::REVIEWER]
        ];
    }
}
```

### 2. Assign Roles to Users
```php
$user = new User();
$roleCollection = new RoleCollection();
$roleCollection->addItem(new BlogPostRole(BlogPostRole::AUTHOR));
$user->setRoles($roleCollection);
```

### 3. Check Permissions
```php
// Check if user can perform specific actions
$user->in(BlogPostRole::AUTHOR);    // true
$user->in(BlogPostRole::COMMENTER); // true (inherited)
$user->in(BlogPostRole::REVIEWER);  // true (inherited)
```

## Architecture Benefits

### 🎯 **Clean Separation**
- **Groups**: Database entities for persistence
- **Roles**: Value objects containing business logic
- **Clear boundaries** between data and behavior

### 🔄 **Automatic Inheritance**
- Reduces administrative overhead
- Prevents permission assignment errors
- Simplifies role management

### 🛡️ **Framework Compatibility**
- Works with existing Bitrix CMS groups
- No database schema changes required
- Backward compatible with string-based roles

### 🧪 **Testable Design**
- Value objects are easy to unit test
- Clear role hierarchies for testing scenarios
- Mockable interfaces for integration tests

## Implementation Status

- ✅ **Core Role System**: Complete with inheritance support
- ✅ **User Integration**: Enhanced `User::in()` method
- ✅ **BlogPost Example**: Working example with 3-level hierarchy
- ✅ **Test Coverage**: Comprehensive unit tests
- ✅ **Documentation**: Complete with examples
- ✅ **Backward Compatibility**: Works with existing code

## Getting Started

1. **Read the [Role System Guide](./roles.md)** for complete documentation
2. **Try the [Examples](./examples/)** to see the system in action
3. **Review the [Design Analysis](./role-design-critique.md)** for architectural insights

## Related Components

- [Domain Layer Overview](../index.md)
- [Value Objects](../valueobject/index.md)
- [Infrastructure Layer](../../infrastructure/index.md)
