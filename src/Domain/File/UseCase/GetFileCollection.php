<?php
declare(strict_types=1);

namespace Domain\File\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\File\Aggregate\FileCollection;
use Domain\File\Infrastructure\Repository\Bitrix\FileRepository;
use Domain\File\Infrastructure\Repository\FileFields;
use Domain\File\Infrastructure\Repository\FileFilter;
use Domain\File\Infrastructure\Repository\FileLimit;
use Domain\File\Infrastructure\Repository\FileRepositoryInterface;
use Domain\File\Infrastructure\Repository\FileSort;
use Domain\Service\ServiceInterface;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 *
 */
class GetFileCollection implements ServiceInterface
{
    //private UserRepositoryInterface $repository;
    public FileManager $manager;
    
    public FileFilter $filter;
    public FileSort $sort;
    public FileLimit $limit;
    public FileFields $fields;

    /**
     *
     * @throws Exception
     */
    public function __construct(/*FileRepositoryInterface $repository*/)
    {
        //$this->repository = $repository;
        $this->manager = new FileManager();
    
        $this->filter   = $this->manager->filter;
        $this->sort     = $this->manager->sort;
        $this->limit    = $this->manager->limit;
        $this->fields   = $this->manager->fields;
    }

    /**
     * @return ?FileCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function __invoke(): ?FileCollection
    {
        return $this->get();
    }

    /**
     * @return ?FileCollection
     * @throws NotAuthorizedException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function get(): ?FileCollection
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
        $this->fields->set([FileRepositoryInterface::ID]);
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
            $this->filter->not(FileRepositoryInterface::ID, $id);
        } else {
            $this->filter->add(FileRepositoryInterface::ID, $id);
        }
        return $this;
    }
    
    /**
     * @param string|string[] $code
     * @param bool $inverse
     * @return $this
     */
    public function filterByCode(string|array $code, bool $inverse = false): self
    {
        if ( $inverse ) {
            $this->filter->not(FileRepositoryInterface::CODE, $code);
        } else {
            $this->filter->add(FileRepositoryInterface::CODE, $code);
        }
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
    
        $UserCollection = $this->fields([$key, $value])->get();
    
        $mappedResult = [];
    
        foreach ( $UserCollection as $access ) {
            $mappedResult[$access->getId()] = $access->getName();
        }
    
        return $mappedResult;
    }
}