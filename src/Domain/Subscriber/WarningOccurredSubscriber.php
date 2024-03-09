<?php
    declare(strict_types=1);
    
    namespace Domain\Subscriber;
    
    use Domain\User\UseCase\UserManager;
    use Domain\Event\Event;
    use Domain\Event\SubscriberInterface;
    use Domain\Events\WarningOccurredEvent;
    use Exception;
    use Infrastructure\Mailer\EmailMessage;
    use Infrastructure\Mailer\MailerManager;

    /**
     * Warning event Subscriber. Sends WarningOccurredEvent message to email $_ENV['WARNING_EMAIL']
     */
    class WarningOccurredSubscriber implements SubscriberInterface
    {
        /**
         * @param WarningOccurredEvent $event
         * @return void
         */
        public function handle(Event $event): void
        {
            if ( !array_key_exists('WARNING_EMAIL', $_ENV) || empty($_ENV['WARNING_EMAIL']) ) {
                return;
            }
            
            try {
                $subject = 'Warning: ' . $event->getWarning();
    
                $body = $subject . '<br />' . PHP_EOL;
                if ( null !== $user = UserManager::getInstance()->getCurrent() ) {
                    $body .= 'User: ' . $user->getFullName() . '<br />' . PHP_EOL;
                } else {
                    $body .= 'User: Not Authorized<br />' . PHP_EOL;
                }
                $body .= 'DateTime: ' . date('Y.m.d H:i:s') . '<br />' . PHP_EOL;
                $body .= 'Page URI: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '<br />' . PHP_EOL;
                $body .= '<pre>' . PHP_EOL;
                $trace = array_map(
                    static function($trace, $i) {
                        return
                            '#' . $i . ': ' . $trace['class'] . $trace['type'] . $trace['function'] . '()' . '<br />' . PHP_EOL .
                            "\t" . $trace['file'] . ':' . $trace['line']
                            ;
                    },
                    $event->getTrace(),
                    array_keys($event->getTrace())
                );
                $body .= implode(PHP_EOL, $trace) . PHP_EOL;
                $body .= '</pre>' . PHP_EOL;
                
                MailerManager::getInstance()->send(
                    (new EmailMessage())
                        ->setTo($_ENV['WARNING_EMAIL'])
                        ->setSubject($subject)
                        ->setBody($body)
                );
            } catch (Exception $e) {
                //
            }
        
        }
    
        /**
         * @param WarningOccurredEvent $event
         * @return bool
         */
        public function isSubscribedTo(Event $event): bool
        {
            return $event instanceof WarningOccurredEvent;
        }
    }