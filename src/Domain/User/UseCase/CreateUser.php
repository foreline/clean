<?php
    declare(strict_types=1);
    
    namespace Domain\User\UseCase;

    use Domain\Event\Publisher;
    use Domain\User\Aggregate\UserInterface;
    use Domain\User\Event\UserCreatedEvent;
    use Exception;

    /**
     * Сервис создания пользователя
     */
    class CreateUser
    {
        /**
         * @param UserInterface $user
         * @return UserInterface
         * @throws Exception
         */
        public function __invoke(UserInterface $user): UserInterface
        {
            return $this->create($user);
        }
        
        /**
         * @param UserInterface $user
         * @return UserInterface
         * @throws Exception
         */
        public function create(UserInterface $user): UserInterface
        {
            $user = UserManager::getInstance()->persist($user);
            Publisher::getInstance()->publish(
                new UserCreatedEvent($user)
            );
            return $user;
        }
    }