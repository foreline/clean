<?php
/**
 * Enhanced User Role System - Demonstration Script
 * 
 * Location: docs/domain/user/examples/role_system_demo.php
 * 
 * This example demonstrates:
 * - Role inheritance hierarchy (AUTHOR → COMMENTER → REVIEWER)
 * - Multiple ways to check user roles
 * - Automatic permission inheritance
 * - Different role assignment levels
 * 
 * To run this example:
 * 1. Copy to your project root directory
 * 2. Run: php role_system_demo.php
 */

// Adjust path based on where you run this example
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Domain\User\Aggregate\User;
use Domain\User\ValueObject\Role;
use Domain\User\ValueObject\BlogPostRole;
use Domain\User\ValueObject\RoleCollection;

echo "=== Enhanced User Role System Demonstration ===\n\n";

// Create a user
$user = new User();

// Create role collection and add an AUTHOR role
$roleCollection = new RoleCollection();
$authorRole = new BlogPostRole(BlogPostRole::AUTHOR);
$roleCollection->addItem($authorRole);
$user->setRoles($roleCollection);

echo "User has been assigned the BlogPostRole::AUTHOR role.\n\n";

// Demonstrate role inheritance
echo "Role Inheritance Demonstration:\n";
echo "- AUTHOR role code: '" . $authorRole->getRole() . "'\n";
echo "- AUTHOR inherited roles: [" . implode(', ', $authorRole->getAllInheritedRoles()) . "]\n\n";

echo "User role checks with inheritance:\n";
echo "- User has AUTHOR role: " . ($user->in(BlogPostRole::AUTHOR) ? 'YES' : 'NO') . "\n";
echo "- User has COMMENTER role: " . ($user->in(BlogPostRole::COMMENTER) ? 'YES' : 'NO') . "\n";
echo "- User has REVIEWER role: " . ($user->in(BlogPostRole::REVIEWER) ? 'YES' : 'NO') . "\n";
echo "- User has non-existent role: " . ($user->in('non_existent') ? 'YES' : 'NO') . "\n\n";

// Demonstrate different ways to check roles
echo "Different ways to check roles:\n";
echo "1. Using string constants:\n";
echo "   - User in 'author': " . ($user->in('author') ? 'YES' : 'NO') . "\n";
echo "   - User in 'commenter': " . ($user->in('commenter') ? 'YES' : 'NO') . "\n";

echo "2. Using Role constants:\n";
echo "   - User in BlogPostRole::AUTHOR: " . ($user->in(BlogPostRole::AUTHOR) ? 'YES' : 'NO') . "\n";
echo "   - User in BlogPostRole::COMMENTER: " . ($user->in(BlogPostRole::COMMENTER) ? 'YES' : 'NO') . "\n";

echo "3. Using Role objects:\n";
$reviewerRoleObj = new BlogPostRole(BlogPostRole::REVIEWER);
echo "   - User in ReviewerRole object: " . ($user->in($reviewerRoleObj) ? 'YES' : 'NO') . "\n";

echo "4. Using multiple roles (variadic):\n";
echo "   - User in any of [AUTHOR, 'non_existent']: " . ($user->in(BlogPostRole::AUTHOR, 'non_existent') ? 'YES' : 'NO') . "\n";
echo "   - User in any of ['non_existent', 'another_non_existent']: " . ($user->in('non_existent', 'another_non_existent') ? 'YES' : 'NO') . "\n\n";

// Demonstrate different role levels
echo "=== Testing Different Role Levels ===\n\n";

// Test COMMENTER role
$commenterUser = new User();
$commenterRoleCollection = new RoleCollection();
$commenterRoleCollection->addItem(new BlogPostRole(BlogPostRole::COMMENTER));
$commenterUser->setRoles($commenterRoleCollection);

echo "User with COMMENTER role:\n";
echo "- Has AUTHOR: " . ($commenterUser->in(BlogPostRole::AUTHOR) ? 'YES' : 'NO') . "\n";
echo "- Has COMMENTER: " . ($commenterUser->in(BlogPostRole::COMMENTER) ? 'YES' : 'NO') . "\n";
echo "- Has REVIEWER: " . ($commenterUser->in(BlogPostRole::REVIEWER) ? 'YES' : 'NO') . "\n\n";

// Test REVIEWER role
$reviewerUser = new User();
$reviewerRoleCollection = new RoleCollection();
$reviewerRoleCollection->addItem(new BlogPostRole(BlogPostRole::REVIEWER));
$reviewerUser->setRoles($reviewerRoleCollection);

echo "User with REVIEWER role:\n";
echo "- Has AUTHOR: " . ($reviewerUser->in(BlogPostRole::AUTHOR) ? 'YES' : 'NO') . "\n";
echo "- Has COMMENTER: " . ($reviewerUser->in(BlogPostRole::COMMENTER) ? 'YES' : 'NO') . "\n";
echo "- Has REVIEWER: " . ($reviewerUser->in(BlogPostRole::REVIEWER) ? 'YES' : 'NO') . "\n\n";

echo "=== Role Information ===\n\n";
echo "Available BlogPost roles:\n";
foreach (BlogPostRole::getAll() as $role) {
    echo "- {$role->getRole()}: '{$role->getName()}'\n";
    echo "  Inherited roles: [" . implode(', ', $role->getAllInheritedRoles()) . "]\n";
}

echo "\n=== Demonstration Complete ===\n";
