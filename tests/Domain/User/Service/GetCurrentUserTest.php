<?php
declare(strict_types=1);

namespace Tests\Domain\User\Service;

use Domain\User\Aggregate\User;
use Domain\User\Service\GetCurrentUser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Domain\User\Service\GetCurrentUser
 */
class GetCurrentUserTest extends TestCase
{
    private GetCurrentUser $getCurrentUser;

    protected function setUp(): void
    {
        $this->getCurrentUser = new GetCurrentUser();
    }

    protected function tearDown(): void
    {
        $this->getCurrentUser->set(null);
        $this->getCurrentUser->setSystemContext(false);
    }

    public function testSystemContextIsDisabledByDefault(): void
    {
        // Arrange & Act & Assert
        $this->assertFalse($this->getCurrentUser->isSystemContext(), 'System context must be disabled by default');
    }

    public function testSetSystemContextTogglesFlag(): void
    {
        // Arrange & Act
        $result = $this->getCurrentUser->setSystemContext(true);

        // Assert
        $this->assertSame($this->getCurrentUser, $result, 'setSystemContext must be fluent');
        $this->assertTrue($this->getCurrentUser->isSystemContext(), 'System context must be enabled after setSystemContext(true)');

        // Act
        $this->getCurrentUser->setSystemContext(false);

        // Assert
        $this->assertFalse($this->getCurrentUser->isSystemContext(), 'System context must be disabled after setSystemContext(false)');
    }

    public function testSetDoesNotAffectSystemContext(): void
    {
        // Arrange
        $this->getCurrentUser->setSystemContext(true);

        // Act
        $this->getCurrentUser->set(new User());

        // Assert
        $this->assertTrue($this->getCurrentUser->isSystemContext(), 'Switching the current user must not reset the system context flag');
    }
}
