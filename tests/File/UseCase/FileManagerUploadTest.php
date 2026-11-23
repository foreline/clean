<?php
declare(strict_types=1);

namespace Tests\File\UseCase;

use Domain\File\Aggregate\File;
use Domain\File\UseCase\FileManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** @covers \Domain\File\UseCase\FileManager::upload */
final class FileManagerUploadTest extends TestCase
{
    /** @dataProvider persistenceOutcomes */
    public function testDownloadedFileIsRemovedAfterPersistence(bool $fail): void
    {
        $manager = $this->getMockBuilder(FileManager::class)
            ->disableOriginalConstructor()->onlyMethods(['persist'])->getMock();
        $path = null;
        $manager->expects(self::once())->method('persist')->willReturnCallback(
            function (File $file) use (&$path, $fail): File {
                $path = $file->getTmpName();
                self::assertSame('avatar', file_get_contents($path));
                if ($fail) {
                    throw new RuntimeException('persistence failed');
                }
                return $file;
            }
        );
        stream_wrapper_unregister('http');
        try {
            stream_wrapper_register('http', AvatarStream::class);
            try {
                $manager->upload('http://asset.example.test/avatar.png');
                self::assertFalse($fail);
            } catch (RuntimeException $e) {
                self::assertTrue($fail);
                self::assertSame('persistence failed', $e->getMessage());
            }
            self::assertFileDoesNotExist($path);
        } finally {
            stream_wrapper_restore('http');
            if (null !== $path && is_file($path)) {
                unlink($path);
            }
        }
    }

    public static function persistenceOutcomes(): array
    {
        return [[false], [true]];
    }
}

final class AvatarStream
{
    public $context;
    private string $remaining = 'avatar';

    public function stream_open($path, $mode, $options, &$openedPath): bool
    {
        return true;
    }

    public function stream_read(int $count): string
    {
        $bytes = substr($this->remaining, 0, $count);
        $this->remaining = substr($this->remaining, strlen($bytes));
        return $bytes;
    }

    public function stream_eof(): bool
    {
        return '' === $this->remaining;
    }

    public function stream_stat(): array
    {
        return [];
    }

    public function url_stat(string $path, int $flags): array
    {
        return [];
    }
}
