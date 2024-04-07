<?php
    declare(strict_types=1);
    
    namespace Domain\File\UseCase;
    
    use Domain\Exception\NotAuthorizedException;
    use Domain\File\Aggregate\File;
    use Domain\User\UseCase\UserManager;
    use Exception;
    use InvalidArgumentException;

    class GetFile
    {
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function __invoke(int $id): File
        {
            return $this->get($id);
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function get(int $id): ?File
        {
            $this->checkPermissions($id);
        
            return FileManager::getInstance()->findById($id);
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws Exception
         */
        public function checkPermissions(int $id): void
        {
            if ( !$user = UserManager::getInstance()->getCurrent() ) {
                throw new NotAuthorizedException();
            }
            // @fixme @todo check permissions
        }
    }