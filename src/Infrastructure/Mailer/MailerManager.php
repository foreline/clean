<?php
    declare(strict_types=1);
    
    namespace Infrastructure\Mailer;

    use Exception;

    /**
     *
     */
    class MailerManager
    {
        /** @var MailerInterface */
        private MailerInterface $mailer;
        
        /** @var ?self */
        private static ?self $instance = null;
    
        /**
         * @param MailerInterface|null $mailer
         * @return self
         */
        public static function getInstance(MailerInterface $mailer = null): self
        {
            if ( !self::$instance ) {
                self::$instance = new self($mailer);
            }
            
            return self::$instance;
        }
    
        /**
         * @param MailerInterface|null $mailer
         */
        private function __construct(MailerInterface $mailer = null)
        {
            $this->mailer = $mailer ?? new Mailer();
        }
    
        /**
         * @param EmailMessage $message
         * @return void
         * @throws Exception
         */
        public function send(EmailMessage $message): void
        {
            if ( defined('DEV_ENV') && true === DEV_ENV ) {
                return;
            }
            $this->mailer->send($message);
        }
    }
    