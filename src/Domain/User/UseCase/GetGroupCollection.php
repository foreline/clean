<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\Service\ServiceInterface;
use Domain\User\Aggregate\GroupCollection;
use Domain\User\Infrastructure\Repository\GroupFields;
use Domain\User\Infrastructure\Repository\GroupFilter;
use Domain\User\Infrastructure\Repository\GroupLimit;
use Domain\User\Infrastructure\Repository\GroupRepositoryInterface;
use Domain\User\Infrastructure\Repository\GroupSort;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 * 
 */
class GetGroupCollection implements ServiceInterface
{
    public GroupManager $manager;
    
    public GroupFilter $filter;
    public GroupSort $sort;
    public GroupLimit $limit;
    public GroupFields $fields;
    
    /**
     *
     */
    public function __construct(/*GroupRepositoryInterface $repository*/)
    {
        //$this->repository = $repository;
        $this->manager = new GroupManager(null, $this);
        
        $this->filter   = $this->manager->filter;
        $this->sort     = $this->manager->sort;
        $this->limit    = $this->manager->limit;
        $this->fields   = $this->manager->fields;
    }
    
    /**
     * @return ?GroupCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(): ?GroupCollection
    {
        return $this->get();
    }
    
    /**
     * @return ?GroupCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(): ?GroupCollection
    {
        $this->checkPermissions();
        
        return $this->manager
            ->filter($this->filter)
            ->sort($this->sort)
            ->limit($this->limit)
            ->fields($this->fields)
            ->find();
    }
    
    /**
     * @return int
     * @throws NotAuthorizedException
     */
    public function count(): int
    {
        $this->fields->set([GroupRepositoryInterface::ID]);
        $this->get();
        return $this->getTotalCount();
    }
    
    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function checkPermissions(): void
    {
        if ( !$user = ( new GetCurrentUser() )->get() ) {
            throw new NotAuthorizedException();
        }
        // @fixme @todo check permissions
    }
    
    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->manager->getTotalCount();
    }
    
    /**
     * @param array $filter
     * @return $this
     */
    public function filter(array $filter): self
    {
        $this->filter->set($filter);
        return $this;
    }
    
    /**
     * @param int|int[] $id
     * @param bool $inverse
     * @return $this
     */
    public function filterById(int|array $id, bool $inverse = false): self
    {
        if ( $inverse ) {
            $this->filter->not(GroupRepositoryInterface::ID, $id);
        } else {
            $this->filter->add(GroupRepositoryInterface::ID, $id);
        }
        return $this;
    }
    
    /**
     * @param bool|bool[] $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): self
    {
        $this->filter->add(GroupRepositoryInterface::ACTIVE, $active);
        return $this;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function filterByExtId(string $extId): self
    {
        $this->filter->add(GroupRepositoryInterface::EXT_ID, $extId);
        return $this;
    }
    
    /**
     * Задает фильтр поиска
     * @param string $term
     * @return $this
     */
    public function search(string $term): self
    {
        $this->filter
            ->byCondition()
                ->searchByName($term)
            ->endCondition()
        ;
        return $this;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function searchByExtId(string $extId): self
    {
        $this->filter->add(GroupRepositoryInterface::EXT_ID, '%' . $extId . '%');
        return $this;
    }
    
    /**
     * @param array<string,string> $sort
     * @return $this
     */
    public function sort(array $sort): self
    {
        $this->sort->set($sort);
        return $this;
    }
    
    /**
     * @param string $field
     * @param string $order
     * @return $this
     */
    public function sortBy(string $field, string $order = 'asc'): self
    {
        $this->sort->by($field, $order);
        return $this;
    }
    
    /**
     * @return $this
     */
    public function sortByRand(): self
    {
        $this->sort->byRand();
        return $this;
    }
    
    /**
     * @param array{limit: int, offset: int, pageNum: int} $limit
     * @return $this
     */
    public function limits(array $limit): self
    {
        $this->limit->set((int)$limit['limit'], (int)$limit['offset'], (int)$limit['pageNum']);
        return $this;
    }
    
    /**
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit): self
    {
        $this->limit->setLimit($limit);
        return $this;
    }
    
    /**
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset): self
    {
        $this->limit->setOffset($offset);
        return $this;
    }
    
    /**
     * @param int $pageNum
     * @return $this
     */
    public function pageNum(int $pageNum): self
    {
        $this->limit->setPageNum($pageNum);
        return $this;
    }
    
    /**
     * @param array $fields
     * @return $this
     */
    public function fields(array $fields): self
    {
        $this->fields->set($fields);
        return $this;
    }
    
    /**
     * @param array $map
     * @return array
     * @throws NotAuthorizedException
     */
    public function map(array $map = ['id' => 'name']): array
    {
        $key = key($map);
        $value = $map[$key];
        
        $GroupCollection = $this->fields([$key, $value])->get();
        
        $mappedResult = [];
        
        foreach ( $GroupCollection as $access ) {
            $mappedResult[$access->getId()] = $access->getName();
        }
        
        return $mappedResult;
    }
}