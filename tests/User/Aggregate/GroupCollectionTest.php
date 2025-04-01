<?php
declare(strict_types=1);

namespace Tests\User\Aggregate;

use Domain\User\Aggregate\Group;
use Domain\User\Aggregate\GroupCollection;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class GroupCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function test__construct(): void
    {
        // Arrange
        $groups = new GroupCollection();
        
        // Act
        $groups->addItem((new Group()));
        
        // Assert
        static::assertEquals(1, $groups->getCount());
    }
}
