<?php
declare(strict_types=1);

namespace Tests\User\Aggregate;

use Domain\User\Aggregate\User;
use Domain\User\Aggregate\UserCollection;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class UserCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function test__construct(): void
    {
        // Arrange
        $users = new UserCollection();
        
        // Act
        $users->addItem((new User()));
        
        // Assert
        static::assertEquals(1, $users->getCount());
    }
}
