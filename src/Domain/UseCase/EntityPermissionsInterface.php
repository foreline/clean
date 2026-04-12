<?php
declare(strict_types=1);

namespace Domain\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\Exception\NotPermittedException;

/**
 * Interface for checking permissions for operations on entities.
 *
 * `mixed` types in the interface vs type-specific signatures in implementations.
 * PHP doesn't support covariant parameter types, so the interface must use `mixed`.
 * Concrete classes retain their typed signatures.
 */
interface EntityPermissionsInterface
{
    /**
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function canCreate(mixed $entity = null): void;
    
    public function checkCanCreate(mixed $entity = null): bool;
    
    /**
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function canUpdate(mixed $entity = null): void;
    
    public function checkCanUpdate(mixed $entity = null): bool;
    
    /**
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function canDelete(mixed $entity = null): void;
    
    public function checkCanDelete(mixed $entity = null): bool;
    
    /**
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function canGet(mixed $entity = null): void;
    
    public function checkCanGet(mixed $entity = null): bool;
    
    /**
     * @throws NotAuthorizedException
     * @throws NotPermittedException
     */
    public function canGetCollection(mixed $service = null): void;
    
    public function checkCanGetCollection(mixed $service = null): bool;
}