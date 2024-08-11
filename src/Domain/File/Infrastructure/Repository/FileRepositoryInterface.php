<?php
    declare(strict_types=1);
    
    namespace Domain\File\Infrastructure\Repository;
    
    use Domain\File\Aggregate\File;
    use Domain\File\Aggregate\FileCollection;
    use Domain\Repository\RepositoryInterface;

    /**
     * File Repository Interface
     */
    interface FileRepositoryInterface extends RepositoryInterface
    {
        public const ID = 'id';
        public const NAME = 'name';
        public const CODE = 'code';
        
        /**
         * @param File $file
         * @return File
         */
        public function persist(File $file): File;
    
        /**
         * @param array $filter
         * @param array $sort
         * @param array $limit
         * @return ?FileCollection
         */
        public function find(array $filter = [], array $sort = [], array $limit = []): ?FileCollection;
    
        /**
         * @param int $fileId
         * @return File|null
         */
        public function findById(int $fileId): ?File;
    
        /**
         * @param int $fileId
         * @return bool
         */
        public function delete(int $fileId): bool;
    }