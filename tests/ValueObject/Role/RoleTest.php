<?php
declare(strict_types=1);

namespace Tests\ValueObject\Role;

use Domain\User\ValueObject\Role;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Domain\User\ValueObject\Role
 */
class RoleTest extends TestCase
{
    public function testRoleCreation(): void
    {
        $role = new Role('admin');
        
        $this->assertEquals('admin', $role->getRole());
        $this->assertEquals('admin', (string) $role);
    }
    
    public function testRoleEquality(): void
    {
        $role1 = new Role('admin');
        $role2 = new Role('admin');
        $role3 = new Role('user');
        
        $this->assertTrue($role1->equals($role2));
        $this->assertFalse($role1->equals($role3));
    }
    
    public function testRoleIs(): void
    {
        $role = new Role('admin');
        
        $this->assertTrue($role->is('admin'));
        $this->assertFalse($role->is('user'));
    }
    
    public function testAdminRole(): void
    {
        $adminRole = (new Role())->admin();
        
        $this->assertEquals('admin', $adminRole->getRole());
        $this->assertTrue($adminRole->isAdmin());
    }
    
    public function testGetAllInheritedRolesWithoutInheritance(): void
    {
        $role = new Role('user');
        $inheritedRoles = $role->getAllInheritedRoles();
        
        $this->assertEquals(['user'], $inheritedRoles);
    }
    
    public function testHasRoleWithoutInheritance(): void
    {
        $role = new Role('user');
        
        $this->assertTrue($role->hasRole('user'));
        $this->assertFalse($role->hasRole('admin'));
    }
    
    public function testHasAnyRoleWithoutInheritance(): void
    {
        $role = new Role('user');
        
        $this->assertTrue($role->hasAnyRole(['user', 'admin']));
        $this->assertTrue($role->hasAnyRole(['user']));
        $this->assertFalse($role->hasAnyRole(['admin', 'moderator']));
    }
    
    public function testGetNameFallback(): void
    {
        $role = new Role('custom_role');
        
        // Should return the role code if name not defined
        $this->assertEquals('custom_role', $role->getName());
    }
    
    public function testGetAll(): void
    {
        $roles = Role::getAll();
        
        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
        $this->assertInstanceOf(Role::class, $roles[0]);
        $this->assertEquals('admin', $roles[0]->getRole());
    }
}
