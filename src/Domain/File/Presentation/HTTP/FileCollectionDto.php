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
        
        $dto = [
            'entityType' => 'fileCollection',
        ];
        
        foreach ( $files as $file ) {
            $dto[] = FileDto::toArray($file, $fields);
        }
        
        return $dto;
    }
}