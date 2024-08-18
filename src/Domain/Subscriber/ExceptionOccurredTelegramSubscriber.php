<?php
declare(strict_types=1);

namespace Domain\Subscriber;

use Domain\Event\Event;
use Domain\Event\SubscriberInterface;
use Domain\Events\ExceptionOccurredEvent;
use Domain\User\Service\GetCurrentUser;
use Exception;

/**
 * Exception Subscriber. Sends ExceptionOccurredEvent message to Telegram chat $_ENV['EXCEPTION_TELEGRAM_CHAT_ID'] using $_ENV['EXCEPTION_TELEGRAM_TOKEN']
 */
class ExceptionOccurredTelegramSubscriber implements SubscriberInterface
{
    /**
     * @param ExceptionOccurredEvent $event
     * @return void
     */
    public function handle(Event $event): void
    {
        if ( !array_key_exists('EXCEPTION_TELEGRAM_TOKEN', $_ENV) || empty($_ENV['EXCEPTION_TELEGRAM_TOKEN']) ) {
            return;
        }

        if ( !array_key_exists('EXCEPTION_TELEGRAM_CHAT_ID', $_ENV) || empty($_ENV['EXCEPTION_TELEGRAM_CHAT_ID']) ) {
            return;
        }
        
        $exception = $event->getException();
        
        try {
            $subject = 'Exception: ' . trim($exception->getMessage());
            
            $body = '<b>' . $subject . '</b>' . PHP_EOL;
            $body .= 'File: ' . $exception->getFile() . ':' . $exception->getLine() . PHP_EOL;
            if ( null !== $user = ( new GetCurrentUser() )->get() ) {
                $body .= 'User: ' . $user->getFullName() . PHP_EOL;
            } else {
                $body .= 'User: Not Authorized' . PHP_EOL;
            }
            $body .= 'DateTime: ' . date('Y.m.d H:i:s') . PHP_EOL;
            $body .= 'Page URI: ' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . PHP_EOL;
            $body .= '$_POST array: ' . PHP_EOL;
            $body .= '<code>';
            $body .= var_export($_POST, true) . PHP_EOL;
            $body .= '</code>';
            $body .= 'Trace:' . PHP_EOL;
            $body .= '<code>' . PHP_EOL;
            $trace = array_map(
                static function($trace, $i) {
                    return
                        '#' . $i . ': ' . $trace['class'] . $trace['type'] . $trace['function'] . '()' . PHP_EOL .
                        "\t" . $trace['file'] . ':' . $trace['line']
                        ;
                },
                $event->getTrace(),
                array_keys($event->getTrace())
            );
            $body .= implode(PHP_EOL, $trace) . PHP_EOL;
            $body .= '</code>' . PHP_EOL;
            
            $apiToken = $_ENV['EXCEPTION_TELEGRAM_TOKEN'];
            
            $data = [
                'chat_id' => $_ENV['EXCEPTION_TELEGRAM_CHAT_ID'],
                'text' => $body,
                'parse_mode'    => 'html',
            ];
            
            file_get_contents('https://api.telegram.org/bot' . $apiToken . '/sendMessage?' . http_build_query($data) );
            
        } catch (Exception $e) {
            // @todo log exception
        }
    }
    
    /**
     * @param ExceptionOccurredEvent $event
     * @return bool
     */
    public function isSubscribedTo(Event $event): bool
    {
        return $event instanceof ExceptionOccurredEvent;
    }
}