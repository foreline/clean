<?php
declare(strict_types=1);

namespace Domain\File\Event;

use Domain\Event\Event;
use Domain\File\Aggregate\File;

/**
 * File updated Event
 */
class FileUpdatedEvent extends Event
{
    private File $file;
    private File $previous;

    /**
     * @param File $file
     * @param File $previous
     */
    public function __construct(File $file, File $previous)
    {
        $this->file = $file;
        $this->previous = $previous;
        parent::__construct();
    }

    /**
     * @return File
     */
    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return File
     */
    public function getPrevious(): File
    {
        return $this->previous;
    }
}