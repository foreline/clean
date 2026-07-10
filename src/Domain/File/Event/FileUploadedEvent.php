<?php
declare(strict_types=1);

namespace Domain\File\Event;

use Domain\Event\Event;
use Domain\File\Aggregate\File;

/**
 * File Uploaded Event
 *
 * Fired when a file has been uploaded (moved from a temporary location
 * or a remote URL into the application's file storage).
 */
class FileUploadedEvent extends Event
{
    private File $file;

    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        parent::__construct();
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }
}
