<?php
    declare(strict_types=1);
    
    namespace Domain\User\UseCase;
    
    use Domain\User\Aggregate\UserInterface;

    /**
     * Сервис удаления пользователя
     */
    class DeleteUser {
        
        public function __invoke(UserInterface $user): bool
        {
            return $this->delete($user);
        }

        /**
         *
         */
        public function delete(UserInterface $user): bool
        {
            // @todo реализовать метод
            return false;
        }
    }