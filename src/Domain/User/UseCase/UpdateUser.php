<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Event\Publisher;
use Domain\Exception\NotAuthorizedException;
use Domain\Exception\NotPermittedException;
use Domain\User\Aggregate\UserInterface;
use Domain\User\Event\UserUpdatedEvent;
use Domain\User\Service\GetCurrentUser;
use Domain\User\ValueObject\Role;
use Exception;

/**
 * Обновление пользователя
 */
class UpdateUser
{
    /**
     * @param UserInterface $user
     * @return UserInterface
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function __invoke(UserInterface $user): UserInterface
    {
        return $this->update($user);
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     * @throws Exception
     */
    public function update(UserInterface $user): UserInterface
    {
        $this->checkPermissions($user);
        
        $user = ( new UserManager() )->persist($user);
        
        Publisher::getInstance()->publish(new UserUpdatedEvent($user));
        
        return $user;
    }

    /**
     * @param UserInterface $user
     * @return void
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     * @throws Exception
     */
    public function checkPermissions(UserInterface $user): void
    {
        if ( !$currentUser = ( new GetCurrentUser() )->get() ) {
            throw new NotAuthorizedException();
        }

        if ( !$currentUser->in(Role::ADMIN) && $currentUser->getId() !== $user->getId() ) {
            throw new NotPermittedException();
        }
    }
}