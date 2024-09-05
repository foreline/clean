<?php
declare(strict_types=1);

namespace Domain\File\Presentation\HTTP;

use Domain\File\Aggregate\File;

/**
 * File HTTP API Data Transfer Object
 */
class FileDto
{
    /**
     * @param File|null $file
     * @param array $fields
     * @return array|null
     */
    public static function toArray(?File $file, array $fields = []): ?array
    {
        if ( !$file ) {
            return null;
        }
        
        $dto = [
            'entityType' => 'file',
        ];
    
        if ( empty($fields) || array_key_exists('id', $fields) ) {
            $dto['id'] = $file->getId();
        }
    
        if ( empty($fields) || array_key_exists('name', $fields) ) {
            $dto['name'] = $file->getName();
        }
    
        if ( empty($fields) || array_key_exists('description', $fields) ) {
            $dto['description'] = $file->getDescription();
        }
    
        if ( empty($fields) || array_key_exists('size', $fields) ) {
            $dto['size'] = $file->getSize();
        }
    
        if ( empty($fields) || array_key_exists('path', $fields) ) {
            $dto['path'] = $file->getPath();
        }
        
        return $dto;
    }
}