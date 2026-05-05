<?php
declare(strict_types=1);

namespace Domain\Subscriber;

use Domain\Event\EventInterface;
use Domain\Event\IndexedSubscriberInterface;
use Domain\User\Service\GetCurrentUser;
use Domain\Events\ExceptionOccurredEvent;
use Exception;
use Infrastructure\Mailer\EmailMessage;
use Infrastructure\Mailer\MailerManager;

/**
 * Обработчик события вызова исключения
 * Exception Subscriber. Sends ExceptionOccurredEvent message to email $_ENV['EXCEPTION_EMAIL']
 */
class ExceptionOccurredSubscriber implements IndexedSubscriberInterface
{
    /**
     * Returns the list of event class names this subscriber handles.
     * Must include every event class that isSubscribedTo() would return true for.
     *
     * @return array<class-string<EventInterface>>
     */
    public static function getSubscribedEvents(): array
    {
        return [ExceptionOccurredEvent::class];
    }
    
    /**
     * @param ExceptionOccurredEvent $event
     * @return void
     */
    public function handle(EventInterface $event): void
    {
        if ( !array_key_exists('EXCEPTION_EMAIL', $_ENV) || empty($_ENV['EXCEPTION_EMAIL']) ) {
            return;
        }
        
        $exception = $event->getException();
        
        try {
            $subject = 'Exception: ' . trim($exception->getMessage());

            $body = $subject . '<br />' . PHP_EOL;
            $body .= 'File: ' . $exception->getFile() . ':' . $exception->getLine() . '<br />' . PHP_EOL;
            if ( null !== $user = ( new GetCurrentUser() )->get() ) {
                $body .= 'User: ' . $user->getFullName() . '<br />' . PHP_EOL;
            } else {
                $body .= 'User: Not Authorized<br />' . PHP_EOL;
            }
            $body .= 'DateTime: ' . date('Y.m.d H:i:s') . '<br />' . PHP_EOL;
            $body .= 'Page URI: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '<br />' . PHP_EOL;
            $body .= '<pre>';
            $body .= 'POST array: ' . var_export($_POST, true) . '<br />' . PHP_EOL;
            $body .= '</pre>';
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

            ( new MailerManager() )->send(
                (new EmailMessage())
                    ->setSubject($subject)
                    ->setTo($_ENV['EXCEPTION_EMAIL'])
                    ->setBody($body)
            );

        } catch (Exception $e) {
        
        }
    }

    /**
     * @param ExceptionOccurredEvent $event
     * @return bool
     */
    public function isSubscribedTo(EventInterface $event): bool
    {
        return $event instanceof ExceptionOccurredEvent;
    }
}