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
            return UserManager::getInstance()->getCurrent();
        }
    }