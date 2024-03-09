<?php
    declare(strict_types=1);
    
    namespace Domain\User\Event;
    
    use Domain\Event\Event;
    use Domain\Event\EventInterface;
    use Domain\User\Aggregate\UserInterface;

    /**
     * Событие подтверждения (емейла) пользователя
     */
    class UserConfirmedEvent extends Event implements EventInterface
    {
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