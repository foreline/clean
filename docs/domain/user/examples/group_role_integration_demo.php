<?php
/**
 * Group-Role Integration - Demonstration Script
 * 
 * Location: docs/domain/user/examples/group_role_integration_demo.php
 * 
 * This example demonstrates:
 * - How to map Bitrix Groups to Domain Roles
 * - Automatic role resolution from group membership
 * - Enhanced User class with effective role calculation
 * - Practical integration with existing systems
 * 
 * To run this example:
 * 1. Copy to your project root directory
 * 2. Run: php group_role_integration_demo.php
 */

// Adjust path based on where you run this example
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Domain\User\Aggregate\User;
use Domain\User\ValueObject\BlogPostRole;
use Domain\User\ValueObject\RoleCollection;

/**
 * Group-Role Mapping Service
 * Solves the ambiguity between Groups (DB) and Roles (Business Logic)
 */
class GroupRoleMapper 
{
    private array $groupToRole = [
        'blog_author' => BlogPostRole::AUTHOR,
        'blog_reviewer' => BlogPostRole::REVIEWER,
        'blog_commenter' => BlogPostRole::COMMENTER,
        'admin' => BlogPostRole::MODERATOR,
    ];
    
    private array $roleToGroup = [
        BlogPostRole::AUTHOR => 'blog_author',
        BlogPostRole::REVIEWER => 'blog_reviewer', 
        BlogPostRole::COMMENTER => 'blog_commenter',
        BlogPostRole::MODERATOR => 'admin',
    ];
    
    /**
     * Get Role from Group membership
     */
    public function getRoleFromGroup(string $groupCode): ?string 
    {
        return $this->groupToRole[$groupCode] ?? null;
    }
    
    /**
     * Get required Group for Role assignment
     */
    public function getRequiredGroup(string $roleCode): ?string 
    {
        return $this->roleToGroup[$roleCode] ?? null;
    }
    
    /**
     * Convert Groups to Roles for a User
     */
    public function getUserRolesFromGroups(User $user): RoleCollection 
    {
        $roleCollection = new RoleCollection();
        $groups = $user->getGroups();
        
        if (!$groups) {
            return $roleCollection;
        }
        
        foreach ($groups->getCollection() as $group) {
            $roleCode = $this->getRoleFromGroup($group->getCode() ?? '');
            if ($roleCode) {
                $roleCollection->addItem(new BlogPostRole($roleCode));
            }
        }
        
        return $roleCollection;
    }
}

/**
 * Enhanced User with automatic Role resolution
 */
class EnhancedUser extends User 
{
    private GroupRoleMapper $mapper;
    
    public function __construct(GroupRoleMapper $mapper = null) 
    {
        parent::__construct();
        $this->mapper = $mapper ?? new GroupRoleMapper();
    }
    
    /**
     * Get effective roles (direct + group-based)
     */
    public function getEffectiveRoles(): RoleCollection 
    {
        $effectiveRoles = new RoleCollection();
        
        // Add direct roles
        $directRoles = $this->getRoles();
        if ($directRoles) {
            foreach ($directRoles->getCollection() as $role) {
                $effectiveRoles->addItem($role);
            }
        }
        
        // Add roles from group membership
        $groupRoles = $this->mapper->getUserRolesFromGroups($this);
        foreach ($groupRoles->getCollection() as $role) {
            $effectiveRoles->addItem($role);
        }
        
        return $effectiveRoles;
    }
    
    /**
     * Enhanced role checking with group fallback
     */
    public function canPerform(string $action): bool 
    {
        $effectiveRoles = $this->getEffectiveRoles();
        
        foreach ($effectiveRoles->getCollection() as $role) {
            if ($role instanceof BlogPostRole && $role->hasRole($action)) {
                return true;
            }
        }
        
        return false;
    }
}

echo "=== Group-Role Integration Demo ===\n\n";

// Simulate Bitrix scenario
$mapper = new GroupRoleMapper();
$user = new EnhancedUser($mapper);

// Simulate user being in 'blog_author' group (from Bitrix DB)
$authorGroup = new \Domain\User\Aggregate\Group();
$authorGroup->setCode('blog_author');
$authorGroup->setName('Blog Authors');

$groupCollection = new \Domain\User\Aggregate\GroupCollection();
$groupCollection->addItem($authorGroup);
$user->setGroups($groupCollection);

echo "User is member of group: 'blog_author'\n";
echo "Mapped role: " . ($mapper->getRoleFromGroup('blog_author') ?? 'none') . "\n\n";

// Test effective roles
$effectiveRoles = $user->getEffectiveRoles();
echo "Effective roles: ";
foreach ($effectiveRoles->getCollection() as $role) {
    echo $role->getRole() . " ";
}
echo "\n\n";

// Test permission checking
echo "Permission checks:\n";
echo "- Can author posts: " . ($user->canPerform(BlogPostRole::AUTHOR) ? 'YES' : 'NO') . "\n";
echo "- Can comment: " . ($user->canPerform(BlogPostRole::COMMENTER) ? 'YES' : 'NO') . "\n";
echo "- Can review: " . ($user->canPerform(BlogPostRole::REVIEWER) ? 'YES' : 'NO') . "\n";

echo "=== Key Benefits ===\n";
echo "✅ Clear Group-Role mapping\n";
echo "✅ Automatic role inheritance from groups\n";
echo "✅ Bitrix compatibility maintained\n";
echo "✅ Flexible permission checking\n";
echo "✅ No database changes required\n";
