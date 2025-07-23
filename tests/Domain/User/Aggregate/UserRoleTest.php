<?php
declare(strict_types=1);

namespace Tests\Domain\User\Aggregate;

use Domain\User\Aggregate\User;
use Tests\Domain\User\ValueObject\BlogPostRole;
use Domain\User\ValueObject\Role;
use Domain\User\ValueObject\RoleCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Domain\User\Aggregate\User
 */
class UserRoleTest extends TestCase
{
    private User $user;
    
    protected function setUp(): void
    {
        $this->user = new User();
    }
    
    public function testUserInWithNoRoles(): void
    {
        $this->assertFalse($this->user->in('admin'));
        $this->assertFalse($this->user->in(Role::ADMIN));
    }
    
    public function testUserInWithEmptyRoleCollection(): void
    {
        $this->user->setRoles(new RoleCollection());
        
        $this->assertFalse($this->user->in('admin'));
        $this->assertFalse($this->user->in(Role::ADMIN));
    }
    
    public function testUserInWithBasicStringRole(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role('admin'));
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in('admin'));
        $this->assertFalse($this->user->in('user'));
    }
    
    public function testUserInWithRoleObject(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role(Role::ADMIN));
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in(new Role(Role::ADMIN)));
        $this->assertFalse($this->user->in(new Role('user')));
    }
    
    public function testUserInWithRoleConstant(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role(Role::ADMIN));
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in(Role::ADMIN));
        $this->assertFalse($this->user->in('user'));
    }
    
    public function testUserInWithBlogPostRoleInheritance(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::AUTHOR));
        $this->user->setRoles($roleCollection);
        
        // Author should have all inherited roles
        $this->assertTrue($this->user->in(BlogPostRole::AUTHOR));
        $this->assertTrue($this->user->in(BlogPostRole::COMMENTER));
        $this->assertTrue($this->user->in(BlogPostRole::REVIEWER));
    }
    
    public function testUserInWithBlogPostCommenterRole(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::COMMENTER));
        $this->user->setRoles($roleCollection);
        
        // Commenter should have commenter and reviewer, but not author
        $this->assertFalse($this->user->in(BlogPostRole::AUTHOR));
        $this->assertTrue($this->user->in(BlogPostRole::COMMENTER));
        $this->assertTrue($this->user->in(BlogPostRole::REVIEWER));
    }
    
    public function testUserInWithBlogPostReviewerRole(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::REVIEWER));
        $this->user->setRoles($roleCollection);
        
        // Reviewer should only have reviewer
        $this->assertFalse($this->user->in(BlogPostRole::AUTHOR));
        $this->assertFalse($this->user->in(BlogPostRole::COMMENTER));
        $this->assertTrue($this->user->in(BlogPostRole::REVIEWER));
    }
    
    public function testUserInWithMultipleRoles(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role('admin'));
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::REVIEWER));
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in('admin'));
        $this->assertTrue($this->user->in(BlogPostRole::REVIEWER));
        $this->assertFalse($this->user->in(BlogPostRole::AUTHOR));
        $this->assertFalse($this->user->in('user'));
    }
    
    public function testUserInWithMultipleRolesAsVariadic(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::AUTHOR));
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in(BlogPostRole::REVIEWER, 'non_existent'));
        $this->assertTrue($this->user->in(BlogPostRole::COMMENTER));
        $this->assertTrue($this->user->in(BlogPostRole::AUTHOR));
        $this->assertFalse($this->user->in('non_existent', 'another_non_existent'));
    }
    
    public function testUserInWithNamespaceSpecificRoleConstants(): void
    {
        // This test simulates namespace-specific role constants
        // In real usage, these would be actual class constants
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::AUTHOR));
        $this->user->setRoles($roleCollection);
        
        // Test that we can check for the specific role code
        $this->assertTrue($this->user->in('author'));
        $this->assertTrue($this->user->in('commenter'));
        $this->assertTrue($this->user->in('reviewer'));
    }
    
    public function testUserInBackwardCompatibilityWithStringRoles(): void
    {
        // Test backward compatibility with old string-based role system
        // Note: This would require modifying RoleCollection to accept strings,
        // but for now we'll test with Role objects containing string codes
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role('admin')); // Using Role object with string code
        $this->user->setRoles($roleCollection);
        
        $this->assertTrue($this->user->in('admin'));
        $this->assertFalse($this->user->in('user'));
    }
    
    public function testAddRole(): void
    {
        $role = new BlogPostRole(BlogPostRole::AUTHOR);
        $this->user->addRole($role);
        
        $this->assertNotNull($this->user->getRoles());
        $this->assertEquals(1, $this->user->getRoles()->getCount());
        $this->assertTrue($this->user->in(BlogPostRole::AUTHOR));
    }
    
    public function testSetAndGetRoles(): void
    {
        $roleCollection = new RoleCollection();
        $roleCollection->addItem(new Role('admin'));
        $roleCollection->addItem(new BlogPostRole(BlogPostRole::REVIEWER));
        
        $this->user->setRoles($roleCollection);
        
        $this->assertSame($roleCollection, $this->user->getRoles());
        $this->assertEquals(2, $this->user->getRoles()->getCount());
    }
}
