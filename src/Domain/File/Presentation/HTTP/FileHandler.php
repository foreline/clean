<?php
declare(strict_types=1);

namespace Domain\File\Presentation\HTTP;

use Domain\File\Aggregate\File;

/**
 * File HTTP API Handler
 */
class FileHandler
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
        
        $result = [];
        
        if ( empty($fields) || array_key_exists('id', $fields) ) {
            $result['id'] = $file->getId();
        }
        
        if ( empty($fields) || array_key_exists('name', $fields) ) {
            $result['name'] = $file->getName();
        }
        
        if ( empty($fields) || array_key_exists('description', $fields) ) {
            $result['description'] = $file->getDescription();
        }
        
        if ( empty($fields) || array_key_exists('size', $fields) ) {
            $result['size'] = $file->getSize();
        }
        
        if ( empty($fields) || array_key_exists('path', $fields) ) {
            $result['path'] = $file->getPath();
        }
        
        return $result;
    }
    
    /**
     * @param ?array $data
     * @return ?File
     */
    public static function fromArray(?array $data): ?File
    {
        if ( null === $data ) {
            return null;
        }
        
        $file = new File();
        
        if ( array_key_exists('id', $data) ) {
            $file->setId((int)$data['id']);
        }
        
        if ( array_key_exists('fileName', $data) ) {
            $file->setFileName((string)$data['fileName']);
        }
        
        if ( array_key_exists('source', $data) ) {
            $file->setSource((string)$data['source']);
        }
        
        return $file;
    }
}