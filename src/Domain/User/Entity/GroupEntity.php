<?php
    declare(strict_types=1);
    
    namespace Domain\User\Entity;

    /**
     *
     */
    class GroupEntity
    {
        private ?int $id = null;
        private bool $active = true;
        private string $name = '';
        private string $description = '';
        private string $code = '';
        private int $sort = 50;
        private bool $anonymous = false;
        private bool $privileged = false;

        /**
         * @param int|null $id
         */
        public function __construct(?int $id = null)
        {
            if ( null !== $id ) {
                $this->setId($id);
            }
        }

        /**
         * @return int|null
         */
        public function getId(): ?int
        {
            return $this->id;
        }

        /**
         * @param int|null $id
         * @return GroupEntity
         */
        public function setId(?int $id): GroupEntity
        {
            $this->id = $id;
            return $this;
        }

        /**
         * @return bool
         */
        public function isActive(): bool
        {
            return $this->active;
        }

        /**
         * @param bool $active
         * @return GroupEntity
         */
        public function setActive(bool $active): GroupEntity
        {
            $this->active = $active;
            return $this;
        }

        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @param string $name
         * @return GroupEntity
         */
        public function setName(string $name): GroupEntity
        {
            $this->name = $name;
            return $this;
        }

        /**
         * @return int
         */
        public function getSort(): int
        {
            return $this->sort;
        }

        /**
         * @param int $sort
         * @return GroupEntity
         */
        public function setSort(int $sort): GroupEntity
        {
            $this->sort = $sort;
            return $this;
        }

        /**
         * @return string
         */
        public function getCode(): string
        {
            return $this->code;
        }

        /**
         * @param string $code
         * @return GroupEntity
         */
        public function setCode(string $code): GroupEntity
        {
            $this->code = $code;
            return $this;
        }

        /**
         * @return bool
         */
        public function isAnonymous(): bool
        {
            return $this->anonymous;
        }

        /**
         * @param bool $anonymous
         * @return GroupEntity
         */
        public function setAnonymous(bool $anonymous): GroupEntity
        {
            $this->anonymous = $anonymous;
            return $this;
        }

        /**
         * @return bool
         */
        public function isPrivileged(): bool
        {
            return $this->privileged;
        }

        /**
         * @param bool $privileged
         * @return GroupEntity
         */
        public function setPrivileged(bool $privileged): GroupEntity
        {
            $this->privileged = $privileged;
            return $this;
        }

        /**
         * @return string
         */
        public function getDescription(): string
        {
            return $this->description;
        }

        /**
         * @param string $description
         * @return GroupEntity
         */
        public function setDescription(string $description): GroupEntity
        {
            $this->description = $description;
            return $this;
        }

    }