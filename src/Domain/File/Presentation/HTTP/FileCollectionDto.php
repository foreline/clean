<?php
declare(strict_types=1);

namespace Domain\File\Presentation\HTTP;

use Domain\File\Aggregate\FileCollection;

/**
 * File Collection HTTP API Data Transfer Object
 */
class FileCollectionDto
{
    /**
     * @param FileCollection|null $files
     * @param array $fields
     * @return array|null
     */
    public static function toArray(?FileCollection $files, array $fields = []): ?array
    {
        if ( !$files ) {
            return null;
        }
        
        $dto = [];
        
        foreach ( $files as $file ) {
            $dto[] = FileDto::toArray($file, $fields);
        }
        
        return $dto;
    }
    
    /**
     * @param ?array $data
     * @return ?FileCollection
     */
    public static function fromArray(?array $data): ?FileCollection
    {
        if ( null === $data ) {
            return null;
        }
        
        $files = new FileCollection();
        
        foreach ( $data as $item ) {
            $files->addItem(FileDto::fromArray($item));
        }
        
        return $files;
    }
}