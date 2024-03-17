<?php
    declare(strict_types=1);
    
    namespace Domain\File\UseCase;

    use Domain\Event\Publisher;
    use Domain\Events\ExceptionOccurredEvent;
    use Domain\Exception\NotAuthorizedException;
    use Domain\File\Aggregate\File;
    use Domain\File\Event\FileCreatedEvent;
    use Domain\User\UseCase\UserManager;
    use Exception;
    use InvalidArgumentException;

    /**
     * File create service
     */
    class CreateFile
    {
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function __invoke(File $file): File
        {
            return $this->create($file);
        }
        
        /**
         * @param File $file
         * @return File $file
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function create(File $file): File
        {
            $this->checkPermissions($file);
        
            $file = FileManager::getInstance()->persist($file);
        
            Publisher::getInstance()->publish(
                new FileCreatedEvent($file)
            );
            return $file;
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws Exception
         * @param ?File $file
         * @return void
         */
        public function checkPermissions(?File $file = null): void
        {
            if ( !UserManager::getInstance()->getCurrent() ) {
                throw new NotAuthorizedException();
            }
            // @fixme @todo check permissions
        }
    
        /**
         * @return bool
         */
        public function userCanAdd(): bool
        {
            try {
                $this->checkPermissions();
            } catch ( Exception $e ) {
                Publisher::getInstance()->publish(new ExceptionOccurredEvent($e));
                return false;
            }
            return true;
        }
    }