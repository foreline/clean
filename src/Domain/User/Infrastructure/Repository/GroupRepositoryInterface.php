<?php
    declare(strict_types=1);
    
    namespace Domain\User\Infrastructure\Repository;
    
    use Domain\User\Aggregate\Group;
    use Domain\User\Aggregate\GroupCollection;

    /**
     * Интерфейс репозитория группы
     */
    interface GroupRepositoryInterface
    {
        public const ID = 'id';
        public const CODE = 'string_id';
        public const ACTIVE = 'active';
        public const NAME = 'name';
        public const DESCRIPTION = 'description';
        public const SORT = 'c_sort';
        public const ANONYMOUS = 'anonymous';
        public const PRIVILEGED = 'privileged';
        
        /**
         * @param Group $group
         * @return int
         */
        public function create(Group $group): int;

        /**
         * @param Group $group
         * @return bool
         */
        public function update(Group $group): bool;

        /**
         * @param int $id
         * @return bool
         */
        public function delete(int $id): bool;
    
        /**
         * @param array $filter
         * @param array $sort
         * @param array $limit
         * @param array $fields
         * @return GroupCollection|null
         * @noinspection PhpTooManyParametersInspection
         */
        public function find(array $filter = [], array $sort = [], array $limit = [], array $fields = []): ?GroupCollection;

        /**
         * @param int $id
         * @return Group|null
         */
        public function findById(int $id): ?Group;
    }