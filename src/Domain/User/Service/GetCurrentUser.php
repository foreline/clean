<?php
    declare(strict_types=1);
    
    namespace Domain\User\Service;

    use Domain\User\Aggregate\User;
    use Domain\User\UseCase\UserManager;
    use Exception;

    /**
     * Сервис. Возвращает текущего авторизованного пользователя
     */
    class GetCurrentUser
    {
        private ?User $currentUser = null;
        
        /**
         * @return User|null
         * @throws Exception
         */
        public function __invoke(): ?User
        {
            return $this->get();
        }
    
        /**
         * @return User|null
         * @throws Exception
         */
        public function get(): ?User
        {
            return $this->currentUser ?? UserManager::getInstance()->getCurrent();
        }
    
        /**
         * @param User $user
         * @return $this
         */
        public function set(User $user): self
        {
            $this->currentUser = $user;
            return $this;
        }
    }