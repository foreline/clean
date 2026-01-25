<?php
declare(strict_types=1);

namespace Domain\File\Presentation\HTTP;

use Domain\File\Aggregate\FileCollection;

/**
 * File Collection HTTP API Handler
 */
class FileCollectionHandler
{
    /**
     * @param FileCollection $files
     * @param array $fields
     * @return array
     */
    public static function toArray(FileCollection $files, array $fields = []): array
    {
        $result = [];
        
        foreach ( $files as $file ) {
            $result[] = FileHandler::toArray($file, $fields);
        }
        
        return $result;
    }
    
    /**
     * @param array $data
     * @return FileCollection
     */
    public static function fromArray(array $data): FileCollection
    {
        $files = new FileCollection();
        
        foreach ( $data as $item ) {
            $files->addItem(FileHandler::fromArray($item));
        }
        
        return $files;
    }
}