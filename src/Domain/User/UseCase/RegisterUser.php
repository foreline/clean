<?php
    declare(strict_types=1);
    
    namespace Domain\User\UseCase;
    
    use Domain\User\Aggregate\UserInterface;
    use Exception;

    /**
     * Сервис. Добавляет (регистрирует) нового пользователя.
     * Высылает письмо с регистрационными данными.
     * @deprecated use \Domain\User\Service\RegisterUser
     */
    class RegisterUser
    {
        /**
         * @param UserInterface $user
         * @return UserInterface
         * @throws Exception
         */
        public function __invoke(UserInterface $user): UserInterface
        {
            return $this->register($user);
        }
    
        /**
         * @param UserInterface $user
         * @return UserInterface
         * @throws Exception
         */
        public function register(UserInterface $user): UserInterface
        {
            $userPassword = substr(md5((string)mt_rand()), 0, 8);
    
            $user->setPassword($userPassword);
            $user->setConfirmPassword($userPassword);
            $user->setLogin($user->getEmail());
            $user->setActive(true);
    
            return (new CreateUser())->create($user);
        }
    }