<?php
    declare(strict_types=1);
    
    namespace Domain\User\Event;

    use Domain\Event\Event;
    use Domain\User\Aggregate\UserInterface;

    /**
     * Событие добавления пользователя
     */
    class UserCreatedEvent extends Event
    {
        /**
         * @var UserInterface
         */
        private UserInterface $user;
    
        /**
         * @param UserInterface $user
         */
        public function __construct(UserInterface $user)
        {
            $this->user = $user;
            parent::__construct();
        }
    
        /**
         * @return UserInterface
         */
        public function getUser(): UserInterface
        {
            return $this->user;
        }
        
    }