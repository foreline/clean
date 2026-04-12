<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\Service\ServiceInterface;
use Domain\User\Aggregate\UserInterface;
use Domain\User\Service\GetCurrentUser;
use Exception;

/**
 * Base class for entity permissions checks.
 */
abstract class AbstractEntityPermissions implements EntityPermissionsInterface
{
    /**
     * Returns the currently authenticated user
     *
     * @return UserInterface
     * @throws NotAuthorizedException
     * @throws Exception
     */
    protected function getAuthenticatedUser(): UserInterface
    {
        if ( !$user = (new GetCurrentUser())->get() ) {
            throw new NotAuthorizedException();
        }
        return $user;
    }
    
    /**
     * Checks if the user has permission to create an entity.
     *
     * @param mixed|null $entity
     * @return bool
     */
    public function checkCanCreate(mixed $entity = null): bool
    {
        try {
            $this->canCreate($entity);
        } catch (Exception) {
            return false;
        }
        return true;
    }
    
    /**
     * Checks if the user has permission to update an entity.
     *
     * @param mixed|null $entity
     * @return bool
     */
    public function checkCanUpdate(mixed $entity = null): bool
    {
        try {
            $this->canUpdate($entity);
        } catch (Exception) {
            return false;
        }
        return true;
    }
    
    /**
     * Checks if the user has permission to delete an entity.
     *
     * @param mixed|null $entity
     * @return bool
     */
    public function checkCanDelete(mixed $entity = null): bool
    {
        try {
            $this->canDelete($entity);
        } catch (Exception) {
            return false;
        }
        return true;
    }
    
    /**
     * Checks if the user has permission to get an entity
     *
     * @param mixed|null $entity
     * @return bool
     */
    public function checkCanGet(mixed $entity = null): bool
    {
        try {
            $this->canGet($entity);
        } catch (Exception) {
            return false;
        }
        return true;
    }
    
    /**
     * Checks if the user has permission to get a collection of entities
     *
     * @param mixed|null|ServiceInterface $service
     * @return bool
     */
    public function checkCanGetCollection(mixed $service = null): bool
    {
        try {
            $this->canGetCollection($service);
        } catch (Exception) {
            return false;
        }
        return true;
    }
}