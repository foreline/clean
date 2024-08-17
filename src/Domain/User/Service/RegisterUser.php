<?php
declare(strict_types=1);

namespace Domain\User\Service;

use Domain\User\Aggregate\UserInterface;
use Domain\User\UseCase\CreateUser;
use Exception;

/**
 * Сервис. Добавляет (регистрирует) нового пользователя.
 * Высылает письмо с регистрационными данными.
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