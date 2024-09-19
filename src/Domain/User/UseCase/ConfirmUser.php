<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Event\Publisher;
use Domain\User\Aggregate\UserInterface;
use Domain\User\Event\UserConfirmedEvent;
use Exception;
use InvalidArgumentException;

/**
 * Сервис подтверждения (емейла) пользователя
 * @deprecated use \Domain\User\Service\ConfirmUser
 */
class ConfirmUser
{
    /**
     * @param UserInterface $user
     * @param string $confirmationCode
     * @return UserInterface
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(UserInterface $user, string $confirmationCode = ''): UserInterface
    {
        return $this->confirm($user, $confirmationCode);
    }

    /**
     * @param UserInterface $user
     * @param string $confirmationCode
     * @return UserInterface
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function confirm(UserInterface $user, string $confirmationCode = ''): UserInterface
    {
        if ( $user->isConfirmed() ) {
            return $user;
        }
        
        if ( '' === $confirmationCode ) {
            throw new InvalidArgumentException('Не задан код подтверждения');
        }
        
        if ( $confirmationCode !== $user->getConfirmationCode() ) {
            throw new InvalidArgumentException('Неверный код подтверждения');
        }
        
        $user->setConfirmationCode('');
        
        $user = ( new UserManager() )->persist($user);
        
        Publisher::getInstance()->publish(new UserConfirmedEvent($user));
        
        return $user;
    }
}