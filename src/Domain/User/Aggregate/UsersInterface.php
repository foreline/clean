<?php
    declare(strict_types=1);
    
    namespace Domain\User\Aggregate;

    /**
     * Интерфейс коллекции пользователей
     */
    interface UsersInterface
    {
    
        /**
         * @return ?UserInterface[]
         */
        //public function getCollection(): ?array;
    
        /**
         * @param UserInterface[] $users
         * @return $this
         */
        //public function setItems(?array $users): self;
    
        /**
         * @param UserInterface $user
         * @return $this
         */
        //public function addItem(UserInterface $user): self;
    
        /**
         * @return string[]|null
         */
        public function getEmails(): ?array;
    }