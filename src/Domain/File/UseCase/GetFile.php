<?php
declare(strict_types=1);

namespace Domain\File\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\File\Aggregate\File;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 *
 */
class GetFile
{
    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(int $id): File
    {
        return $this->get($id);
    }

    /**
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(int $id, bool $checkPermissions = true): ?File
    {
        if ( $checkPermissions ) {
            $this->checkPermissions($id);
        }
        if ( !$id ) {
            throw new InvalidArgumentException('File ID is missing');
        }
        return ( new FileManager() )->findById($id);
    }

    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function checkPermissions(int $id): void
    {
        /*if ( !$user = ( new GetCurrentUser() )->get() ) {
            throw new NotAuthorizedException();
        }*/
        // @fixme @todo check permissions
    }
}