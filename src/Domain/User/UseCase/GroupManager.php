<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Repository\FieldsInterface;
use Domain\Repository\FilterInterface;
use Domain\Repository\LimitInterface;
use Domain\Repository\SortInterface;
use Domain\Service\ServiceInterface;
use Domain\UseCase\AbstractManager;
use Domain\User\Aggregate\Group;
use Domain\User\Aggregate\GroupCollection;
use Domain\User\Infrastructure\Repository\Bitrix\GroupRepository;
use Domain\User\Infrastructure\Repository\GroupFields;
use Domain\User\Infrastructure\Repository\GroupFilter;
use Domain\User\Infrastructure\Repository\GroupLimit;
use Domain\User\Infrastructure\Repository\GroupRepositoryInterface;
use Domain\User\Infrastructure\Repository\GroupSort;

/**
 *
 */
class GroupManager extends AbstractManager implements ServiceInterface
{
    private ?ServiceInterface $service;
    
    private static ?self $instance = null;
    private GroupRepositoryInterface $repository;
    
    public FilterInterface|GroupFilter $filter;
    public SortInterface|GroupSort $sort;
    public LimitInterface|GroupLimit $limit;
    public FieldsInterface|GroupFields $fields;
    
    /**
     * @param GroupRepositoryInterface|null $repository
     * @return self
     * @deprecated
     */
    public static function getInstance(GroupRepositoryInterface $repository = null): self
    {
        if ( !self::$instance ) {
            self::$instance = new self($repository);
        }
        return self::$instance;
    }
    
    /**
     * @param GroupRepositoryInterface|null $repository
     */
    public function __construct(GroupRepositoryInterface $repository = null, ?ServiceInterface $service = null)
    {
        $this->service = $service ?? $this;
        $this->repository = $repository ?? new GroupRepository($this->service);
        parent::__construct();
    }

    /**
     * @param Group $group
     * @return int
     */
    private function create(Group $group): int
    {
        return $this->repository->create($group);
    }

    /**
     * @param Group $group
     * @return bool
     */
    private function update(Group $group): bool
    {
        return $this->repository->update($group);
    }

    /**
     * @param Group $group
     * @return Group
     */
    public function persist(Group $group): Group
    {
        if ( 0 < $group->getId() ) {
            $this->update($group);
        } else {
            $id = $this->create($group);
            $group->setId($id);
        }

        return $this->findById($group->getId());
    }

    /**
     * @return ?GroupCollection
     */
    public function find(): ?GroupCollection
    {
        $groups = $this->repository->find(
            $this->filter->get(),
            $this->sort->get(),
            $this->limit->getLimits(),
            $this->fields->get()
        );
        $this->reset();
        return $groups;
    }
    
    /**
     * @param int $id
     * @return Group|null
     */
    public function findById(int $id): ?Group
    {
        $group = $this->repository->findById($id);
        $this->reset();
        return $group;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @param string|string[] $code
     * @return $this
     */
    public function filterByCode(string|array $code): self
    {
        $this->filter->add($this->repository::CODE, $code);
        return $this;
    }

    /**
     * @param int|int[] $id
     * @return $this
     */
    public function filterById(int|array $id): self
    {
        $this->filter->add($this->repository::ID, $id);
        return $this;
    }

    /**
     * @param bool|bool[] $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): self
    {
        $this->filter->add($this->repository::ACTIVE, $active);
        return $this;
    }
    
}