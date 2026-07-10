<?php
declare(strict_types=1);

namespace Domain\File\UseCase;

use Domain\Event\Publisher;
use Domain\File\Aggregate\File;
use Domain\File\Aggregate\FileCollection;
use Domain\File\Event\FileUploadedEvent;
use Exception;
use InvalidArgumentException;

/**
 * File upload use case.
 *
 * This is the permission-aware, event-emitting entry point for uploading files.
 * It supports both single-file uploads (from a local path or URL) and
 * multi-file uploads from the PHP $_FILES structure.
 *
 * @todo Add permission checks once the framework provides a FilePermissions strategy.
 */
class UploadFile
{
    /**
     * Upload a single file from a local path or URL.
     *
     * @param string $sourcePathOrUrl Local file path or remote URL.
     * @param string $originalName Original file name. If empty, basename of the source is used.
     * @param string $description Optional file description.
     * @param bool $raiseEvents Whether to emit domain events.
     * @return File
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(
        string $sourcePathOrUrl,
        string $originalName = '',
        string $description = '',
        bool $raiseEvents = true,
    ): File {
        return $this->upload($sourcePathOrUrl, $originalName, $description, $raiseEvents);
    }

    /**
     * Upload a single file from a local path or URL.
     *
     * @param string $sourcePathOrUrl Local file path or remote URL.
     * @param string $originalName Original file name. If empty, basename of the source is used.
     * @param string $description Optional file description.
     * @param bool $raiseEvents Whether to emit domain events.
     * @return File
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function upload(
        string $sourcePathOrUrl,
        string $originalName = '',
        string $description = '',
        bool $raiseEvents = true,
    ): File {
        $file = (new FileManager())->upload($sourcePathOrUrl, $originalName, $description);

        if ( $raiseEvents ) {
            Publisher::getInstance()->publish(new FileUploadedEvent($file));
        }

        return $file;
    }

    /**
     * Upload multiple files from the PHP $_FILES structure.
     *
     * @param array $userFile Files array from $_FILES, e.g. $_FILES['files'].
     * @param string[] $descriptions Array of descriptions keyed by file index.
     * @param bool $raiseEvents Whether to emit domain events for each uploaded file.
     * @return FileCollection|null
     * @throws Exception
     */
    public function uploadFiles(
        array $userFile,
        array $descriptions = [],
        bool $raiseEvents = true,
    ): ?FileCollection {
        $files = (new FileManager())->uploadFiles($userFile, $descriptions);

        if ( null === $files ) {
            return null;
        }

        if ( $raiseEvents ) {
            foreach ( $files->getCollection() as $file ) {
                Publisher::getInstance()->publish(new FileUploadedEvent($file));
            }
        }

        return $files;
    }
}
