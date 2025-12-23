<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\User\Infrastructure\Repository\UserFields;
use Domain\User\Infrastructure\Repository\UserRepositoryInterface;

/**
 * User fields
 */
trait UserFieldsTrait
{
    public UserFields $fields;
    
    /**
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields): static
    {
        $this->fields->set($fields);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function selectId(): static
    {
        $this->fields->add(UserRepositoryInterface::ID);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function selectActive(): static
    {
        $this->fields->add(UserRepositoryInterface::ACTIVE);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function selectLogin(): static
    {
        $this->fields->add(UserRepositoryInterface::LOGIN);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function selectEmail(): static
    {
        $this->fields->add(UserRepositoryInterface::EMAIL);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function selectName(): static
    {
        $this->fields->add(UserRepositoryInterface::NAME);
        return $this;
    }
}