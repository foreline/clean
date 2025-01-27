<?php
declare(strict_types=1);

namespace Domain\User\Infrastructure\Repository;

use Domain\Repository\ConditionFilter;
use Domain\Repository\ConditionFilterInterface;
use Domain\Repository\FilterInterface;

/**
 *
 */
class UserConditionFilter extends ConditionFilter implements ConditionFilterInterface
{
    private UserFilter $userFilter;
    private array $condition;
    
    /**
     * @param UserFilter|FilterInterface $filter
     */
    public function __construct(UserFilter|FilterInterface $filter)
    {
        $this->condition['LOGIC'] = 'OR';
        $this->userFilter = $filter;
    }
    
    
    /**
     * @param string $name
     * @return $this
     */
    public function searchByName(string $name): self
    {
        $this->condition[UserRepositoryInterface::NAME] = '%' . $name . '%';
        return $this;
    }
    
    
    /**
     * @param string $lastName
     * @return $this
     */
    public function searchByLastName(string $lastName): self
    {
        $this->condition[UserRepositoryInterface::LAST_NAME] = '%' . $lastName . '%';
        return $this;
    }
    
    /**
     * @param string $secondName
     * @return $this
     */
    public function searchBySecondName(string $secondName): self
    {
        $this->condition[UserRepositoryInterface::SECOND_NAME] = '%' . $secondName . '%';
        return $this;
    }
    
    /**
     * @param string $email
     * @return $this
     */
    public function searchByEmail(string $email): self
    {
        $this->condition[UserRepositoryInterface::EMAIL] = '%' . $email . '%';
        return $this;
    }
    
    /**
     * @param string $phone
     * @return $this
     */
    public function searchByPhone(string $phone): self
    {
        $this->condition[UserRepositoryInterface::PHONE] = '%' . $phone . '%';
        return $this;
    }
    
    /**
     * @param string $login
     * @return $this
     */
    public function searchByLogin(string $login): self
    {
        $this->condition[UserRepositoryInterface::LOGIN] = '%' . $login . '%';
        return $this;
    }
    
    /**
     * @param string $position
     * @return $this
     */
    public function searchByPosition(string $position): self
    {
        $this->condition[UserRepositoryInterface::POSITION] = '%' . $position . '%';
        return $this;
    }
    
    /**
     * @param string $department
     * @return $this
     */
    public function searchByDepartment(string $department): self
    {
        $this->condition[UserRepositoryInterface::DEPARTMENT] = '%' . $department . '%';
        return $this;
    }
    
    /**
     * @param string $id
     * @return $this
     */
    public function searchById(string $id): self
    {
        $this->condition[UserRepositoryInterface::ID] = '%' . $id . '%';
        return $this;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function searchByExtId(string $extId): self
    {
        $this->condition[UserRepositoryInterface::EXT_ID] = '%' . $extId . '%';
        return $this;
    }
    
    /**
     * @return UserFilter
     */
    public function endCondition(): UserFilter
    {
        $this->userFilter->add('condition', $this->condition);
        return $this->userFilter;
    }
}