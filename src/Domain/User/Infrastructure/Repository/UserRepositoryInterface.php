<?php
    declare(strict_types=1);
    
    namespace Domain\User\Infrastructure\Repository;

    use Domain\User\Aggregate\UserInterface;
    use Domain\User\Aggregate\UsersInterface;

    /**
     * Интерфейс репозитория пользователей
     */
    interface UserRepositoryInterface
    {
        public const ID = 'id';
        public const LOGIN = 'login';
        public const EMAIL = 'email';
        public const PASSWORD = 'password';
        public const CONFIRM_PASSWORD = 'confirm_password';
        public const NAME = 'name';
        public const LAST_NAME = 'last_name';
        public const ACTIVE = 'active';
        public const PHONE = 'personal_phone';
        public const DEPARTMENT = 'work_department';
        public const POSITION = 'work_position';
        public const GROUPS = 'group_id';
        public const CONFIRM_CODE = 'confirm_code';
        
        /**
         * @param UserInterface $user
         * @return UserInterface
         */
        public function persist(UserInterface $user): UserInterface;
    
        /**
         * @param array $filter
         * @param array $sort
         * @param array $limit
         * @param array $fields
         * @return ?UsersInterface
         * @noinspection PhpTooManyParametersInspection
         */
        public function find(array $filter = [], array $sort = [], array $limit = [], array $fields = []): ?UsersInterface;
    
        /**
         * @param int $id
         * @param array $fields
         * @return ?UserInterface
         */
        public function findById(int $id, array $fields = []): ?UserInterface;

        /**
         * @param int $id
         * @return bool
         */
        public function delete(int $id): bool;
    
        /**
         * @param array $userFields
         * @return ?UserInterface
         */
        public function getCurrentUser(array $userFields = []): ?UserInterface;
    
        /**
         * Является ли текущий пользователем администратором
         * @return bool
         */
        public function isAdmin(): bool;
    
        /**
         * Авторизован ли текущий пользователь
         * @return bool
         */
        public function isAuthorized(): bool;
    
        /**
         * @return int
         */
        public function getCount(): int;
    
        /**
         * @return int
         */
        public function getTotalCount(): int;
        
    }