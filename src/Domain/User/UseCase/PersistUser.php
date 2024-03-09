<?php
    declare(strict_types=1);
    
    namespace Domain\User\UseCase;
    
    use Domain\Exception\NotAuthorizedException;
    use Domain\Exception\NotPermittedException;
    use Domain\User\Aggregate\User;
    use Exception;
    use InvalidArgumentException;

    /**
     * Persist User Service.
     * Calls CreateUser Service for new user.
     * Calls UpdateUser Service for existing user.
     */
    class PersistUser
    {
        /**
         * @throws NotAuthorizedException
         * @throws NotPermittedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function __invoke(User $user): User
        {
            return $this->persist($user);
        }
    
        /**
         * @throws NotAuthorizedException
         * @throws NotPermittedException
         * @throws InvalidArgumentException
         * @throws Exception
         */
        public function persist(User $access): User
        {
            if ( 0 < $access->getId() ) {
                return (new UpdateUser())($access);
            } else {
                return (new CreateUser())($access);
            }
        }
    }