<?php
declare(strict_types=1);

namespace Domain\File\Event;

use Domain\Event\Event;
use Domain\File\Aggregate\File;

/**
 * File persisted event
 */
class FilePersistedEvent extends Event
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