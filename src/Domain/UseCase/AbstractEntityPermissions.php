<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Aggregate\AggregateInterface;
use Domain\Exception\NotAuthorizedException;
use Domain\Service\ServiceInterface;
use Domain\User\Aggregate\UserInterface;
use Domain\User\Service\GetCurrentUser;
use Exception;

/**
 * Base class for entity permissions checks.
 *
 * Concrete classes must implement can* methods with entity-specific type hints.
 * The checkCan* methods are inherited and should NOT be overridden.
 *
 * @method void canCreate(?AggregateInterface $entity = null)
 * @method void canUpdate(?AggregateInterface $entity = null)
 * @method void canDelete(?AggregateInterface $entity = null)
 * @method void canGet(?AggregateInterface $entity = null)
 * @method void canGetCollection(?ServiceInterface $service = null)
 */
abstract class AbstractEntityPermissions
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
     * @param ?AggregateInterface $entity
     * @return bool
     */
    public function checkCanCreate(?AggregateInterface $entity = null): bool
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
     * @param ?AggregateInterface $entity
     * @return bool
     */
    public function checkCanUpdate(?AggregateInterface $entity = null): bool
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
     * @param ?AggregateInterface $entity
     * @return bool
     */
    public function checkCanDelete(?AggregateInterface $entity = null): bool
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
     * @param ?AggregateInterface $entity
     * @return bool
     */
    public function checkCanGet(?AggregateInterface $entity = null): bool
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
     * @param ?ServiceInterface $service
     * @return bool
     */
    public function checkCanGetCollection(?ServiceInterface $service = null): bool
    {
        try {
            $this->canGetCollection($service);
        } catch (Exception) {
            return false;
        }
        return true;
    }
}