<?php
declare(strict_types=1);

namespace Domain\File\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\File\Aggregate\FileCollection;
use Domain\File\Infrastructure\Repository\FileRepositoryInterface;
use Domain\UseCase\Fields;
use Domain\UseCase\Filter;
use Domain\UseCase\Limit;
use Domain\UseCase\Sort;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;
use Domain\File\Infrastructure\Repository\Bitrix\FileRepository;

/**
 *
 */
class GetFileCollection
{
    //private UserRepositoryInterface $repository;
    public FileManager $manager;
    public Filter $filter;
    public Sort $sort;
    public Limit $limit;
    public Fields $fields;

    /**
     *
     * @throws Exception
     */
    public function __construct(/*FileRepositoryInterface $repository*/)
    {
        //$this->repository = $repository;
        $this->manager = new FileManager();
    
        $this->filter = new Filter();
        $this->sort = new Sort();
        $this->limit = new Limit();
        $this->fields = new Fields();
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