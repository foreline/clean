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
     * Признак системного контекста выполнения (service account):
     * код исполняется от имени системного пользователя в фоновых сценариях
     * (cron, webhook, async worker), где нет интерактивного пользователя
     */
    private static bool $systemContext = false;
    
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
        if ( null === self::$currentUser ) {
            self::$currentUser = ( new UserManager() )->getCurrent();
        }
        return self::$currentUser;
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

    /**
     * Включает/выключает системный контекст выполнения.
     * Устанавливается сервисами подмены пользователя (например, RunAsSystemUser)
     * на время исполнения callback и гарантированно восстанавливается после него
     * @param bool $systemContext
     * @return $this
     */
    public function setSystemContext(bool $systemContext): self
    {
        self::$systemContext = $systemContext;
        return $this;
    }

    /**
     * Активен ли системный контекст выполнения
     * @return bool
     */
    public function isSystemContext(): bool
    {
        return self::$systemContext;
    }
}