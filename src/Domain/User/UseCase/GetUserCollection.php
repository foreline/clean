<?php
    declare(strict_types=1);
    
    namespace Domain\User\UseCase;
    
    use Domain\User\Infrastructure\Repository\Bitrix\UserRepository;
    use Domain\Exception\NotAuthorizedException;
    use Domain\UseCase\Fields;
    use Domain\UseCase\Filter;
    use Domain\UseCase\Limit;
    use Domain\UseCase\Sort;
    use Domain\User\Aggregate\UserCollection;
    use Exception;
    use InvalidArgumentException;

    /**
     *
     */
    class GetUserCollection
    {
        public UserManager $manager;
        public Filter $filter;
        public Sort $sort;
        public Limit $limit;
        public Fields $fields;
    
        /**
         * 
         */
        public function __construct()
        {
            $this->manager = UserManager::getInstance();
        
            $this->filter = new Filter();
            $this->sort = new Sort();
            $this->limit = new Limit();
            $this->fields = new Fields();
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
            $this->fields->set([UserRepository::ID]);
            $this->get();
            return $this->getTotalCount();
        }
        
        /**
         * @throws NotAuthorizedException
         * @throws Exception
         */
        public function checkPermissions(): void
        {
            if ( !$user = UserManager::getInstance()->getCurrent() ) {
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
                $this->filter->not(UserRepository::ID, $id);
            } else {
                $this->filter->add(UserRepository::ID, $id);
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
                $this->filter->not(UserRepository::CODE, $code);
            } else {
                $this->filter->add(UserRepository::CODE, $code);
            }
            return $this;
        }
        
        /**
         * @param int|int[] $protocolId
         * @return $this
         */
        public function filterByProtocol(int|array $protocolId): self
        {
            $this->filter->add(UserRepository::PROTOCOL, $protocolId);
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