<?php
declare(strict_types=1);

namespace Tests\Infrastructure\Event\Debounce;

use Domain\Event\DebounceState;
use Infrastructure\Event\Debounce\FileDebounceStorage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Infrastructure\Event\Debounce\FileDebounceStorage
 */
class FileDebounceStorageTest extends TestCase
{
    private string $directory;

    private FileDebounceStorage $storage;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'debounce-test-' . uniqid('', true);
        $this->storage = new FileDebounceStorage($this->directory);
    }

    protected function tearDown(): void
    {
        foreach ( glob($this->directory . DIRECTORY_SEPARATOR . '*') ?: [] as $file ) {
            @unlink($file);
        }
        @rmdir($this->directory);
    }

    private function makeState(string $key, int $dueAtMs): DebounceState
    {
        return new DebounceState(
            $key,
            'Some\\Subscriber',
            'Some\\Event',
            serialize(['payload' => $key]),
            $dueAtMs,
            $dueAtMs - 1000,
            0,
        );
    }

    public function testSaveAndGetRoundTrip(): void
    {
        // Arrange
        $state = $this->makeState('badge:u1', 5000);

        // Act
        $this->storage->save($state);
        $loaded = $this->storage->get('badge:u1');

        // Assert
        $this->assertNotNull($loaded);
        $this->assertSame('badge:u1', $loaded->getKey());
        $this->assertSame(5000, $loaded->getDueAtMs());
        $this->assertSame('Some\\Subscriber', $loaded->getSubscriberClass());
    }

    public function testGetReturnsNullForUnknownKey(): void
    {
        $this->assertNull($this->storage->get('missing'));
    }

    public function testSaveOverwritesExistingKey(): void
    {
        // Arrange
        $this->storage->save($this->makeState('badge:u1', 5000));

        // Act
        $this->storage->save($this->makeState('badge:u1', 9000));
        $loaded = $this->storage->get('badge:u1');

        // Assert
        $this->assertNotNull($loaded);
        $this->assertSame(9000, $loaded->getDueAtMs());
    }

    public function testDeleteRemovesState(): void
    {
        // Arrange
        $this->storage->save($this->makeState('badge:u1', 5000));

        // Act
        $this->storage->delete('badge:u1');

        // Assert
        $this->assertNull($this->storage->get('badge:u1'));
    }

    public function testDueReturnsOnlyElapsedStates(): void
    {
        // Arrange
        $this->storage->save($this->makeState('a', 1000));
        $this->storage->save($this->makeState('b', 2000));
        $this->storage->save($this->makeState('c', 3000));

        // Act
        $due = $this->storage->due(2000);

        // Assert
        $keys = array_map(static fn(DebounceState $s): string => $s->getKey(), $due);
        sort($keys);
        $this->assertSame(['a', 'b'], $keys);
    }

    public function testDueIgnoresCorruptFiles(): void
    {
        // Arrange — a valid state plus a corrupt file in the directory.
        $this->storage->save($this->makeState('a', 1000));
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . 'garbage.json', '{not valid json');

        // Act
        $due = $this->storage->due(5000);

        // Assert
        $this->assertCount(1, $due);
        $this->assertSame('a', $due[0]->getKey());
    }
}
