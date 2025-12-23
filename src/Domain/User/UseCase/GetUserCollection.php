<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Domain\Exception\NotAuthorizedException;
use Domain\Service\ServiceInterface;
use Domain\User\Aggregate\UserCollection;
use Domain\User\Infrastructure\Repository\UserRepositoryInterface;
use Domain\User\Service\GetCurrentUser;
use Exception;
use InvalidArgumentException;

/**
 *
 */
class GetUserCollection implements ServiceInterface
{
    public UserManager $manager;
    
    use UserFilterTrait;
    use UserSortTrait;
    use UserLimitTrait;
    use UserFieldsTrait;
    
    /**
     *
     */
    public function __construct()
    {
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