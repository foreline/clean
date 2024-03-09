<?php
    
    namespace Infrastructure\Mailer;

    /**
     * Интерфейс почтовой службы
     */
    interface MailerInterface
    {
        /**
         * @param MessageInterface $message
         * @return mixed
         */
        public function send(MessageInterface $message);
    }