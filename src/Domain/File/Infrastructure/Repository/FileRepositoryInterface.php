<?php
declare(strict_types=1);

namespace Domain\File\Infrastructure\Repository;

use Domain\File\Aggregate\File;
use Domain\File\Aggregate\FileCollection;
use Domain\Repository\RepositoryInterface;
use JetBrains\PhpStorm\Deprecated;

/**
 * File Repository Interface
 */
interface FileRepositoryInterface extends RepositoryInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const CODE = 'code';
    public const DESCRIPTION = 'description';
    public const ORIGINAL_NAME = 'original_name';
    public const FILE_SIZE = 'file_size';
    public const FILE_NAME = 'file_name';
    
    /**
     * @param File $file
     * @return File
     */
    public function persist(File $file): File;

    /**
     * @param array $filter
     * @param array $sort
     * @param array $limit
     * @return ?FileCollection
     */
    public function find(#[Deprecated]array $filter = [], #[Deprecated]array $sort = [], #[Deprecated]array $limit = []): ?FileCollection;

    /**
     * @param int $fileId
     * @return File|null
     */
    public function findById(int $fileId): ?File;

    /**
     * @param int $fileId
     * @return bool
     */
    public function delete(int $fileId): bool;
}