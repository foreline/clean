<?php
declare(strict_types=1);

namespace Tests\File\Aggregate;

use Domain\File\Aggregate\File;
use Domain\File\Aggregate\FileCollection;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class FileCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function test__construct(): void
    {
        // Arrange
        $files = new FileCollection();
        
        // Act
        $files->addItem((new File()));
        
        // Assert
        static::assertEquals(1, $files->getCount());
    }
}
