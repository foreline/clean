<?php
declare(strict_types=1);

namespace Tests\Domain\User\UseCase;

use Domain\Exception\NotPermittedException;
use Domain\User\Aggregate\User;
use Domain\User\Service\GetCurrentUser;
use Domain\User\UseCase\UpdateUser;
use Domain\User\ValueObject\Role;
use Domain\User\ValueObject\RoleCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Domain\User\UseCase\UpdateUser
 */
class UpdateUserTest extends TestCase
{
    private GetCurrentUser $getCurrentUser;
    private UpdateUser $updateUser;

    protected function setUp(): void
    {
        $this->getCurrentUser = new GetCurrentUser();
        $this->updateUser = new UpdateUser();
    }

    protected function tearDown(): void
    {
        $this->getCurrentUser->set(null);
        $this->getCurrentUser->setSystemContext(false);
    }

    /**
     * @param int $id
     * @param string ...$roles
     * @return User
     */
    private function makeUser(int $id, string ...$roles): User
    {
        $user = (new User())->setId($id);

        $roleCollection = new RoleCollection();
        foreach ( $roles as $role ) {
            $roleCollection->addItem(new Role($role));
        }
        $user->setRoles($roleCollection);

        return $user;
    }

    public function testCheckPermissionsThrowsWhenUpdatingAnotherUserWithoutAdminRole(): void
    {
        // Arrange
        $this->getCurrentUser->set($this->makeUser(1, 'user'));

        // Assert
        $this->expectException(NotPermittedException::class);

        // Act
        $this->updateUser->checkPermissions($this->makeUser(2));
    }

    public function testCheckPermissionsAllowsSelfUpdate(): void
    {
        // Arrange
        $this->getCurrentUser->set($this->makeUser(1, 'user'));

        // Act & Assert (no exception expected)
        $this->updateUser->checkPermissions($this->makeUser(1));
        $this->addToAssertionCount(1);
    }

    public function testCheckPermissionsAllowsAdmin(): void
    {
        // Arrange
        $this->getCurrentUser->set($this->makeUser(1, Role::ADMIN));

        // Act & Assert (no exception expected)
        $this->updateUser->checkPermissions($this->makeUser(2));
        $this->addToAssertionCount(1);
    }

    public function testCheckPermissionsAllowsSystemContext(): void
    {
        // Arrange
        $this->getCurrentUser->set($this->makeUser(1, 'system'));
        $this->getCurrentUser->setSystemContext(true);

        // Act & Assert (no exception expected)
        $this->updateUser->checkPermissions($this->makeUser(2));
        $this->addToAssertionCount(1);
    }

    public function testCheckPermissionsStillThrowsWhenSystemContextDisabled(): void
    {
        // Arrange
        $this->getCurrentUser->set($this->makeUser(1, 'system'));
        $this->getCurrentUser->setSystemContext(false);

        // Assert
        $this->expectException(NotPermittedException::class);

        // Act
        $this->updateUser->checkPermissions($this->makeUser(2));
    }
}
