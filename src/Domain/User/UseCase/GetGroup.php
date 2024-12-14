<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\User\Aggregate\Group;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 * Get Group Service
 */
class GetGroup
{
    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(int $id): Group
    {
        return $this->get($id);
    }

    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(int $id): ?Group
    {
        $this->checkPermissions($id);
    
        return ( new GroupManager() )->findById($id);
    }
    
    /**
     * @param string $code
     * @return Group|null
     * @throws NotAuthorizedException
     */
    public function getByCode(string $code): ?Group
    {
        if ( !$group = (new GroupManager())->filterByCode($code)->find()?->current() ) {
            return null;
        }
        
        $this->checkPermissions($group->getId());
        
        return $group;
    }

    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function checkPermissions(int $id): void
    {
        if ( !( new GetCurrentUser() )->get() ) {
            throw new NotAuthorizedException();
        }
        // @fixme @todo check permissions
    }
}