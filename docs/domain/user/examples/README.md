# User Role System Examples

This directory contains practical examples demonstrating the Enhanced User Role System implementation.

## Available Examples

### 1. Role System Demo (`role_system_demo.php`)
**Purpose**: Demonstrates the core role inheritance functionality

**Features Covered**:
- ✅ Role inheritance hierarchy (AUTHOR → COMMENTER → REVIEWER)
- ✅ Multiple ways to check user roles (strings, constants, objects)
- ✅ Automatic permission inheritance
- ✅ Different role assignment levels
- ✅ Effective roles calculation

**Usage**:
```bash
# Copy to project root and run:
php role_system_demo.php
```

### 2. Group-Role Integration Demo (`group_role_integration_demo.php`)
**Purpose**: Shows how to integrate with existing Group systems (like Bitrix)

**Features Covered**:
- ✅ Group-to-Role mapping strategies
- ✅ Automatic role resolution from group membership
- ✅ Enhanced User class with effective role calculation
- ✅ Backward compatibility with existing systems
- ✅ Practical integration patterns

**Usage**:
```bash
# Copy to project root and run:
php group_role_integration_demo.php
```

## Running the Examples

### Prerequisites
- PHP 8.1+ with the Clean Architecture framework installed
- Composer autoloader configured

### Quick Start
1. Copy any example file to your project root directory
2. Ensure the autoloader path is correct (adjust `require_once` if needed)
3. Run the example: `php example_file.php`

### Expected Output

#### Role System Demo Output:
```
=== Enhanced User Role System Demonstration ===

User has been assigned the BlogPostRole::AUTHOR role.

Role Inheritance Demonstration:
- AUTHOR role code: 'author'
- AUTHOR inherited roles: [author, commenter, reviewer]

User role checks with inheritance:
- User has AUTHOR role: YES
- User has COMMENTER role: YES  (inherited)
- User has REVIEWER role: YES   (inherited)
...
```

#### Group-Role Integration Demo Output:
```
=== Group-Role Integration Demo ===

User is member of group: 'blog_author'
Mapped role: author

Effective roles: author commenter reviewer

Permission checks:
- Can author posts: YES
- Can comment: YES (inherited)
- Can review: YES (inherited)
...
```

## Understanding the Examples

### Role Hierarchy Concept
```
AUTHOR (highest level)
├── COMMENTER (inherited)
│   └── REVIEWER (inherited)
└── REVIEWER (inherited)
```

This means:
- An AUTHOR can perform all COMMENTER and REVIEWER actions
- A COMMENTER can perform all REVIEWER actions  
- A REVIEWER can only perform REVIEWER actions

### Integration Benefits
- 🎯 **Decoupled Logic**: Business logic separate from database Groups
- 🔄 **Automatic Inheritance**: No need to assign multiple groups
- 🛡️ **Bitrix Compatibility**: Works with existing group systems
- 🧪 **Easy Testing**: Clear role boundaries for unit tests
- 📈 **Scalable**: Each domain can define its own roles

## Related Documentation
- [Role System Documentation](../roles.md)
- [Design Critique & Analysis](../role-design-critique.md)
- [Domain Layer Overview](../../index.md)
