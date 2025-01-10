<?php
declare(strict_types=1);

namespace Domain\User\Service;

use Domain\User\Aggregate\UserInterface;
use Domain\User\UseCase\UserManager;
use Exception;

/**
 * Сервис. Возвращает текущего авторизованного пользователя
 */
class GetCurrentUser
{
    private static ?UserInterface $currentUser = null;
    
    /**
     * @return UserInterface|null
     * @throws Exception
     */
    public function __invoke(): ?UserInterface
    {
        return $this->get();
    }
    
    /**
     * Возвращает текущего пользователя
     * @return UserInterface|null
     * @throws Exception
     */
    public function get(): ?UserInterface
    {
        return self::$currentUser ?? ( new UserManager() )->getCurrent();
    }
    
    /**
     * Авторизация и деавторизация пользователя
     * @param ?UserInterface $user
     * @return $this
     */
    public function set(?UserInterface $user): self
    {
        self::$currentUser = $user;
        return $this;
    }
}