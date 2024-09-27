<?php
declare(strict_types=1);

namespace Domain\User\UseCase;

use Bitrix\Main\Engine\CurrentUser;

use CGroup;
use CUser;
use Domain\Repository\FieldsInterface;
use Domain\Repository\FilterInterface;
use Domain\Repository\LimitInterface;
use Domain\Repository\SortInterface;
use Domain\UseCase\AbstractManager;
use Domain\UseCase\ServiceInterface;
use Domain\User\Aggregate\User;
use Domain\User\Aggregate\UserInterface;
use Domain\User\Aggregate\UserCollection;
use Domain\User\Infrastructure\Repository\Bitrix\UserRepository;
use Domain\User\Infrastructure\Repository\GroupRepositoryInterface;
use Domain\User\Infrastructure\Repository\UserFields;
use Domain\User\Infrastructure\Repository\UserFilter;
use Domain\User\Infrastructure\Repository\UserLimit;
use Domain\User\Infrastructure\Repository\UserRepositoryInterface;
use Domain\User\Infrastructure\Repository\UserSort;
use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Менеджер пользователя.
 * Не рекомендуется использовать напрямую, а только через сервисы
 * Singleton Pattern.
 */
class UserManager extends AbstractManager implements ServiceInterface
{
    private ?ServiceInterface $service;
    
    /** @var UserRepositoryInterface */
    private UserRepositoryInterface $repository;
    
    /** @var User|null  */
    private static ?User $currentUser = null;
    
    public FilterInterface|UserFilter $filter;
    public FieldsInterface|UserFields $fields;
    public SortInterface|UserSort     $sort;
    public LimitInterface|UserLimit   $limit;
    
    /**
     *
     */
    public function __construct(UserRepositoryInterface $repository = null, ?ServiceInterface $service = null)
    {
        $this->service = $service ?? $this;
        $this->repository = $repository ?? new UserRepository($this->service);
        
        parent::__construct();
        
        $this->filter   = $this->repository->filter;
        $this->fields   = $this->repository->fields;
        $this->sort     = $this->repository->sort;
        $this->limit    = $this->repository->limit;
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     * @throws Exception
     */
    public function persist(UserInterface $user): UserInterface
    {
        return $this->repository->persist($user);
    }

    /**
     * @param int $userId
     * @param array $fields
     * @return ?UserInterface
     * @throws Exception
     */
    public function findById(int $userId, array $fields = []): ?UserInterface
    {
        if ( 0 >= $userId ) {
            //throw new \InvalidArgumentException('Не задан ID пользователя');
            return null;
        }
        $user = $this->repository->findById($userId, $this->fields->get($fields));
        $this->reset();
        return $user;
    }

    /**
     * @return ?UserCollection
     * @throws Exception
     */
    public function find(): ?UserCollection
    {
        $users = $this->repository->find(
            $this->filter->get(),
            $this->sort->get(),
            $this->limit->getLimits(),
            $this->fields->get()
        );
        
        $this->reset();
        
        return $users;
    }

    /**
     * @param int $userID
     * @return array
     */
    public function getUserGroup(int $userID): array
    {
        return CUser::GetUserGroup($userID); // @fixme переписать на D7
    }

    /**
     * Возвращает роли (группы) пользователя.
     * @param int $userId ID пользователя. Если не задан, выбирается текущий пользователь
     * @return array $arRoles Массив с символьными кодами групп
     * @throws Exception
     */
    public function getRoles(int $userId = 0): array
    {
        if ( 0 >= $userId && $this->getCurrentUser() ) {
            $userId = $this->getCurrentUser()->getId();
        }
        
        if ( !$userId ) {
            return [];
        }
        
        $arGroupIDs = $this->getUserGroup($userId);

        $arGroups = [];

        foreach ( $arGroupIDs as $groupID ) {

            $arGroup = CGroup::GetByID($groupID)->Fetch();

            if ( 'Y' === $arGroup['ANONYMOUS'] ) {
                continue;
            }

            $arGroups[] = $arGroup['STRING_ID'];
        }

        return $arGroups;
    }

    /**
     * Является ли текущий пользователь администратором.
     * @return bool
     * @throws Exception
     */
    public function isAdmin(): bool
    {
        return $this->repository->isAdmin();
    }

    /**
     * Авторизован ли текущий пользователь
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->repository->isAuthorized();
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        if ( !$this->isAdmin() ) {
            return false;
        }
        return $this->repository->delete($id);
    }

    /**
     * @param array $fields
     * @return UserInterface|null
     * @throws Exception
     */
    public function getCurrentUser(array $fields = []): ?UserInterface
    {
        if ( null === self::$currentUser ) {
            self::$currentUser = $this->repository->getCurrentUser($fields);
        }
        return self::$currentUser;
    }
    
    /**
     * @param int $offset
     * @return UserManager
     */
    public function setOffset(int $offset): UserManager
    {
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * @param bool|bool[] $active
     * @return $this
     */
    public function filterByActive(bool|array $active = true): self
    {
        $this->filter->add(UserRepositoryInterface::ACTIVE, $active);
        return $this;
    }

    /**
     * @param int $groupId
     * @return UserManager
     */
    public function filterByGroupId(int $groupId): self
    {
        $this
            ->filter->add(UserRepositoryInterface::GROUPS, $groupId);
        return $this;
    }
    
    /**
     * @param string $groupCode
     * @return $this
     */
    public function filterByGroupCode(string $groupCode): self
    {
        // @fixme не делать запрос
        $groupManager = new GroupManager();
        $groupManager->fields->set([GroupRepositoryInterface::ID]);
        
        $groupId = $groupManager
            ->filterByCode($groupCode)
            ->find()?->current()?->getId();
        
        $this
            ->filter->add(UserRepositoryInterface::GROUPS, $groupId);
        
        return $this;
    }

    /**
     * @return UserInterface|null
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function getCurrent(): ?UserInterface
    {
        if ( !$currentUserId = (int)CurrentUser::get()->getId() ) {
            return null;
        }
        if ( null === self::$currentUser ) {
            self::$currentUser = $this->findById($currentUserId);
        }
        return self::$currentUser;
    }

    /**
     * @param int $userId
     * @return void
     */
    public function authorize(int $userId): void
    {
        self::$currentUser = null;
        //global $USER;
        //$USER?->Authorize($userId);
        
        (new CUser())->Authorize($userId);
    }
    
    /*
     * @param string $fieldCode
     * @return mixed
     */
    /*public function getUserField(string $fieldCode): mixed
    {
        return $this->repository->getUserField($fieldCode);
    }*/

    /**
     * Сортирует пользователей по фамилии, имени
     * @param string $order
     * @return $this
     */
    public function sortByName(string $order = 'asc'): self
    {
        $this
            ->sort->add('last_name', $order);
        $this
            ->sort->add('name', $order);
        return $this;
    }

    /**
     * @param string $roleCode
     * @return $this
     */
    public function filterByRole(string $roleCode): self
    {
        // @fixme
        $this
            ->filter->add(UserRepositoryInterface::GROUPS, $roleCode);
        return $this;
    }

    /**
     * @param string ...$rolesCode
     * @return $this
     */
    public function filterByRoles(string ...$rolesCode): self
    {
        // @fixme
        $this
            ->filter->add(UserRepositoryInterface::GROUPS, $rolesCode);
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->repository->getTotalCount();
    }
}