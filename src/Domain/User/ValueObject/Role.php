<?php
    declare(strict_types=1);
    
    namespace Domain\User\ValueObject;

    use Domain\ValueObject\ValueObjectInterface;

    /**
     * Роль пользователя
     */
    class Role implements ValueObjectInterface
    {
        public const ADMIN = 'admin';
        
        private string $role;
        
        private array $names = [
            self::ADMIN     => 'Администратор',
        ];
        
        /**
         * @param string $role
         */
        public function __construct(string $role)
        {
            $this->role = $role;
        }
    
        /**
         * @return string
         */
        public function getName(): string
        {
            return $this->names[$this->role];
        }
    
        /**
         * @return $this
         */
        public function admin(): self
        {
            return new self(self::ADMIN);
        }
    
        /**
         * @return bool
         */
        public function isAdmin(): bool
        {
            return $this->equals(self::admin());
        }
    
        /**
         * @return string
         */
        public function getRole(): string
        {
            return $this->role;
        }
    
        /**
         * @param Role $role
         * @return bool
         */
        public function equals(self $role): bool
        {
            return $this->role === $role->role;
        }
    
        /**
         * @param string $roleCode
         * @return bool
         */
        public function is(string $roleCode): bool
        {
            return $this->getRole() === $roleCode;
        }
    
        /**
         * @return array
         */
        public static function getAll(): array
        {
            return [
                (new self(self::ADMIN)),
            ];
        }
    
        /**
         * @return string
         */
        public function __toString(): string
        {
            return $this->getRole();
        }
    }