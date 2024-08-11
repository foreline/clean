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
         * @param MailerInterface $mailer
         * @return self
         * @deprecated
         */
        public static function getInstance(MailerInterface $mailer): self
        {
            if ( !self::$instance ) {
                self::$instance = new self($mailer);
            }
            
            return self::$instance;
        }
    
        /**
         * @param MailerInterface|null $mailer
         */
        public function __construct(MailerInterface $mailer = null)
        {
            $this->mailer = $mailer;
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
            if ( 'dev' === mb_strtolower($_ENV['APP_ENV']) ) {
                return;
            }
            $this->mailer->send($message);
        }
    }
    