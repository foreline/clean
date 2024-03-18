<?php
    declare(strict_types=1);
    
    namespace Domain\File\UseCase;

    use Domain\Event\Publisher;
    use Domain\Exception\NotAuthorizedException;
    use Domain\File\Aggregate\File;
    use Domain\File\Event\FileUpdatedEvent;
    use Domain\User\UseCase\UserManager;
    use Exception;
    use InvalidArgumentException;

    /**
     * File update service
     */
    class UpdateFile
    {
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function __invoke(File $file): File
        {
            return $this->update($file);
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function update(File $file): File
        {
            $this->checkPermissions($file);
        
            $previous = clone $file;
        
            $file = FileManager::getInstance()->persist($file);
        
            Publisher::getInstance()->publish(
                new FileUpdatedEvent($file, $previous)
            );
            return $file;
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws Exception
         */
        public function checkPermissions(File $file): void
        {
            if ( !UserManager::getInstance()->getCurrent() ) {
                throw new NotAuthorizedException();
            }
            // @fixme @todo check permissions
        }
    }