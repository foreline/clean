<?php
declare(strict_types=1);

namespace Infrastructure\Event\Config;

use Infrastructure\Event\Message\AsyncEventMessage;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Middleware\SendMessageMiddleware;
use Symfony\Component\Messenger\Retry\MultiplierRetryStrategy;
use Symfony\Component\Messenger\Transport\Sender\SendersLocator;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Factory for creating and configuring Symfony Messenger components.
 *
 * This factory is framework-agnostic. It accepts pre-built transport
 * and handler instances, then wires the Symfony Messenger bus,
 * middleware, and routing.
 *
 * Transport and handler creation is the responsibility of the consuming
 * application (via bootstrap files, DI containers, etc.).
 */
final class MessengerFactory
{
    /**
     * Creates a Symfony Messenger bus that routes AsyncEventMessage to the given transport.
     *
     * The bus includes SendMessageMiddleware (routes messages to the transport)
     * and HandleMessageMiddleware (handles messages when consumed by the worker).
     *
     * @param TransportInterface $transport The async transport (database, Redis, etc.)
     * @param callable $handler The message handler callable (e.g. AsyncEventMessageHandler)
     * @return MessageBusInterface Configured message bus
     */
    public static function createMessageBus(TransportInterface $transport, callable $handler): MessageBusInterface
    {
        $sendersLocator = new SendersLocator(
            [AsyncEventMessage::class => ['async']],
            self::createTransportContainer($transport)
        );
        
        $handlersLocator = new HandlersLocator([
            AsyncEventMessage::class => [$handler],
        ]);
        
        return new MessageBus([
            new SendMessageMiddleware($sendersLocator),
            new HandleMessageMiddleware($handlersLocator),
        ]);
    }
    
    /**
     * Creates a message bus configured for the worker (consumer) side.
     *
     * The worker bus only needs HandleMessageMiddleware since messages
     * are already received from the transport — no sending needed.
     *
     * @param callable $handler The message handler callable (e.g. AsyncEventMessageHandler)
     * @return MessageBusInterface Worker message bus
     */
    public static function createWorkerBus(callable $handler): MessageBusInterface
    {
        $handlersLocator = new HandlersLocator([
            AsyncEventMessage::class => [$handler],
        ]);
        
        return new MessageBus([
            new HandleMessageMiddleware($handlersLocator),
        ]);
    }
    
    /**
     * Creates a retry strategy for failed message processing.
     *
     * Uses exponential backoff: 60s → 120s → 240s (max 600s / 10 minutes).
     *
     * @param int $maxRetries Maximum retry attempts (default: 3)
     * @param int $delayMs Base delay in milliseconds (default: 60000 = 60s)
     * @param float $multiplier Delay multiplier per retry (default: 2.0)
     * @return MultiplierRetryStrategy
     */
    public static function createRetryStrategy(
        int $maxRetries = 3,
        int $delayMs = 60000,
        float $multiplier = 2.0,
    ): MultiplierRetryStrategy {
        return new MultiplierRetryStrategy(
            maxRetries: $maxRetries,
            delayMilliseconds: $delayMs,
            multiplier: $multiplier,
            maxDelayMilliseconds: 600000, // 10 minutes max
        );
    }
    
    /**
     * Creates a PSR-11 container that resolves transports by name.
     *
     * @param TransportInterface $asyncTransport The async transport instance
     * @return ContainerInterface
     */
    private static function createTransportContainer(TransportInterface $asyncTransport): ContainerInterface
    {
        return new class ($asyncTransport) implements ContainerInterface {
            public function __construct(
                private readonly TransportInterface $transport,
            ) {}
            
            public function get(/*string */$id): TransportInterface
            {
                if ( 'async' !== $id ) {
                    throw new InvalidArgumentException("Unknown transport: {$id}");
                }
                return $this->transport;
            }
            
            public function has(/*string */$id): bool
            {
                return 'async' === $id;
            }
        };
    }
}
