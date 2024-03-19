<?php
    declare(strict_types=1);
    
    namespace Infrastructure\Mailer;

    /**
     * Mail service Interface
     */
    interface MailerInterface
    {
        /**
         * @param MessageInterface $message
         * @return void
         */
        public function send(MessageInterface $message): void;
        
    }