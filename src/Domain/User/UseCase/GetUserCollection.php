<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\UseCase\ServiceInterface;
use Domain\User\Aggregate\UserCollection;
use Domain\User\Infrastructure\Repository\UserFields;
use Domain\User\Infrastructure\Repository\UserFilter;
use Domain\User\Infrastructure\Repository\UserLimit;
use Domain\User\Infrastructure\Repository\UserRepositoryInterface;
use Domain\User\Infrastructure\Repository\UserSort;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 *
 */
class GetUserCollection implements ServiceInterface
{
    //private UserRepositoryInterface $repository;
    public UserManager $manager;
    
    public UserFilter $filter;
    public UserSort $sort;
    public UserLimit $limit;
    public UserFields $fields;

    /**
     *
     */
    public function __construct(/*UserRepositoryInterface $repository*/)
    {
        //$this->repository = $repository;
        $this->manager = new UserManager(null, $this);
    
        $this->filter   = $this->manager->filter;
        $this->sort     = $this->manager->sort;
        $this->limit    = $this->manager->limit;
        $this->fields   = $this->manager->fields;
    }
    
    /**
     * @return ?UserCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(): ?UserCollection
    {
        return $this->get();
    }
    
    /**
     * @return ?UserCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(): ?UserCollection
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
        $this->fields->set([UserRepositoryInterface::ID]);
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
     * @param array $sort
     * @return $this
     */
    public function sort(array $sort): self
    {
        $this->sort->set($sort);
        return $this;
    }
    
    /**
     * @param array{limit: int, offset: int, pageNum: int} $limit
     * @return $this
     */
    public function limit(array $limit): self
    {
        $this->limit->set((int)$limit['limit'], (int)$limit['offset'], (int)$limit['pageNum']);
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
    
        $UserCollection = $this->fields([$key, $value])->get();
    
        $mappedResult = [];
    
        foreach ( $UserCollection as $access ) {
            $mappedResult[$access->getId()] = $access->getName();
        }
    
        return $mappedResult;
    }
}