<?php
    declare(strict_types=1);
    
    namespace Domain\User\Aggregate;

    use Domain\Aggregate\AggregateInterface;
    use Domain\Entity\EntityInterface;
    use Domain\File\Aggregate\File;
    use Domain\User\ValueObject\Role;
    use Domain\User\ValueObject\RoleCollection;
    use Domain\User\Entity\UserEntity;

    /**
     * User aggregate
     */
    class User extends UserEntity implements AggregateInterface, UserInterface
    {
        private ?RoleCollection $roles = null;
        private ?GroupCollection $groups = null;
        private ?File $avatar = null;
        
        /** @var string  */
        private string $slug = '';
        /** @var string  */
        private string $addSlug = '';
    
        /**
         * @return string
         */
        public function getSlug(): string
        {
            return $this->slug;
        }
    
        /**
         * @param string $slug
         * @return $this
         */
        public function setSlug(string $slug): static
        {
            $this->slug = $slug;
            return $this;
        }
    
        /**
         * @return RoleCollection|null
         */
        public function getRoles(): ?RoleCollection
        {
            return $this->roles;
        }
    
        /**
         * @param Role $role
         * @return $this
         */
        public function addRole(Role $role): self
        {
            if ( null === $this->roles ) {
                $this->roles = new RoleCollection();
            }
            $this->roles->addItem($role);
            return $this;
        }
        
        /**
         * @param RoleCollection|null $roles
         * @return $this
         */
        public function setRoles(?RoleCollection $roles): self
        {
            $this->roles = $roles;
            return $this;
        }
        
        /**
         * @param string ...$rolesCode
         * @return bool
         */
        public function in(...$rolesCode): bool
        {
            if ( null === $userRoles = $this->getRoles() ) {
                return false;
            }
            
            if ( 0 === $userRoles->getCount() ) {
                return false;
            }
            
            foreach ( $rolesCode as $roleCode ) {
                if ( in_array($roleCode, $userRoles->getCollection()) ) {
                    return true;
                }
            }
            
            return false;
        }
    
        /**
         * @return GroupCollection|null
         */
        public function getGroups(): ?GroupCollection
        {
            return $this->groups;
        }
    
        /**
         * @param GroupCollection|null $groups
         * @return $this
         */
        public function setGroups(?GroupCollection $groups): self
        {
            $this->groups = $groups;
            return $this;
        }
    
        /**
         * @param Group $group
         * @return $this
         */
        public function addGroup(Group $group): self
        {
            if ( null === $this->groups ) {
                $this->groups = new GroupCollection();
            }
            $this->groups->addItem($group);
            return $this;
        }
    
        /**
         * @return File|null
         */
        public function getAvatar(): ?File
        {
            return $this->avatar;
        }
    
        /**
         * @param File|null $avatar
         * @return User
         */
        public function setAvatar(?File $avatar): User
        {
            $this->avatar = $avatar;
            return $this;
        }
    
        /**
         * Gets Entity add/edit page url
         * @return string
         */
        public function getAddSlug(): string
        {
            return $this->addSlug;
        }
    
        /**
         * Sets Entity add/edit page url
         * @param string $addSlug
         * @return $this
         */
        public function setAddSlug(string $addSlug): static
        {
            $this->addSlug = $addSlug;
            return $this;
        }
        
        /**
         * @param array $fields
         * @return array
         */
        public function toArray(array $fields = []): array
        {
            return [
                'entity_type'   => 'user',
                'id'    => $this->getId(),
                'name'  => $this->getFirstName(),
                'lastName'  => $this->getLastName(),
                'fullName'  => $this->getFullName(),
                'active'    => $this->isActive(),
                'slug'      => $this->getSlug(),
                // @todo
            ];
        }
    }