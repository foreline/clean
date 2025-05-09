<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\User\Aggregate\User;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 *
 */
class GetUser
{
    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(int $id): User
    {
        return $this->get($id);
    }
    
    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(int $id, bool $checkPermissions = true): ?User
    {
        try {
            if ( $checkPermissions ) {
                $this->checkPermissions($id);
            }
            return ( new UserManager() )->findById($id);
        } catch ( NotAuthorizedException $e ) {
            return null;
        }
    }
    
    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function checkPermissions(int $id): void
    {
        if ( !$user = ( new GetCurrentUser() )->get() ) {
            throw new NotAuthorizedException();
        }
        // @fixme @todo check permissions
    }
}