<?php
declare(strict_types=1);

namespace Domain\File\Presentation\HTTP;

use Domain\File\Aggregate\File;

/**
 * File HTTP API Handler
 */
class FileHandler
{
    public const ID = 'id';
    public const NAME = 'name';
    public const FILE_NAME = 'fileName';
    public const DESCRIPTION = 'description';
    public const SIZE = 'size';
    public const PATH = 'path';
    public const SOURCE = 'source';
    
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
        
        if ( empty($fields) || array_key_exists(self::ID, $fields) ) {
            $result[self::ID] = $file->getId();
        }
        
        if ( empty($fields) || array_key_exists(self::NAME, $fields) ) {
            $result[self::NAME] = $file->getName();
        }
        
        if ( empty($fields) || array_key_exists(self::DESCRIPTION, $fields) ) {
            $result[self::DESCRIPTION] = $file->getDescription();
        }
        
        if ( empty($fields) || array_key_exists(self::SIZE, $fields) ) {
            $result[self::SIZE] = $file->getSize();
        }
        
        if ( empty($fields) || array_key_exists(self::PATH, $fields) ) {
            $result[self::PATH] = $file->getPath();
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
        
        if ( array_key_exists(self::ID, $data) ) {
            $file->setId((int)$data[self::ID]);
        }
        
        if ( array_key_exists(self::FILE_NAME, $data) ) {
            $file->setFileName((string)$data[self::FILE_NAME]);
        }
        
        if ( array_key_exists(self::SOURCE, $data) ) {
            $file->setSource((string)$data[self::SOURCE]);
        }
        
        return $file;
    }
}