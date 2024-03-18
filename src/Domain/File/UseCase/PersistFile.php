<?php
    declare(strict_types=1);
    
    namespace Domain\File\UseCase;

    use Domain\Event\Publisher;
    use Domain\Exception\NotAuthorizedException;
    use Domain\File\Aggregate\File;
    use Domain\File\Event\FilePersistedEvent;
    use Exception;
    use InvalidArgumentException;

    /**
     * Persist file service
     */
    class PersistFile
    {
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function __invoke(File $file): File
        {
            return $this->persist($file);
        }
        
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function persist(File $file): File
        {
            if ( 0 < $file->getId() ) {
                $file = (new UpdateFile())($file);
            } else {
                $file = (new CreateFile())($file);
            }
        
            Publisher::getInstance()->publish(
                new FilePersistedEvent($file)
            );
        
            return $file;
        }
    }