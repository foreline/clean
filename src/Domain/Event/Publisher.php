<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Event subscription and publication
 */
class Publisher
{
    /** @var SubscriberInterface[] All registered subscribers (for deduplication checks and getSubscribers()). */
    private array $subscribers;
    
    /** @var SubscriberInterface[] Only non-indexed subscribers — iterated during fallback scan. */
    private array $legacySubscribers = [];
    
    /**
     * Event index for IndexedSubscriberInterface implementors.
     * Structure: eventClass => [ priority => [SubscriberInterface, ...] ]
     * Higher priority = dispatched first. Built incrementally at subscribe() time.
     *
     * @var array<class-string<EventInterface>, array<int, SubscriberInterface[]>>
     */
    private array $index = [];
    
    /**
     * @var Publisher|null $this
     */
    private static ?self $instance = null;
    
    /**
     * @var AsyncDispatcherInterface|null
     */
    private ?AsyncDispatcherInterface $asyncDispatcher = null;
    
    /**
     *
     */
    private function __construct()
    {
        $this->subscribers = [];
    }
    
    /**
     * Sets the async dispatcher for handling AsyncSubscriberInterface subscribers.
     * When not set, all subscribers are processed synchronously (backward compatible).
     *
     * @param AsyncDispatcherInterface $asyncDispatcher
     */
    public function setAsyncDispatcher(AsyncDispatcherInterface $asyncDispatcher): void
    {
        $this->asyncDispatcher = $asyncDispatcher;
    }
    
    /**
     * Returns the async dispatcher if set.
     *
     * @return AsyncDispatcherInterface|null
     */
    public function getAsyncDispatcher(): ?AsyncDispatcherInterface
    {
        return $this->asyncDispatcher;
    }
    
    /**
     * @return Publisher
     */
    public static function getInstance(): self
    {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        
        return static::$instance;
    }
    
    /**
     * Event subscription.
     * Indexed subscribers (IndexedSubscriberInterface) are registered in the event index
     * for O(1) dispatch. All other subscribers use the legacy linear scan as fallback.
     *
     * @param SubscriberInterface $subscriber
     * @param int $priority Higher value = dispatched earlier within the same event. Default 0.
     */
    public function subscribe(SubscriberInterface $subscriber, int $priority = 0): void
    {
        if ( in_array($subscriber, $this->subscribers, false) ) {
            return;
        }
        
        $this->subscribers[] = $subscriber;
        
        if ( $subscriber instanceof IndexedSubscriberInterface ) {
            foreach ( $subscriber::getSubscribedEvents() as $eventClass ) {
                $this->index[$eventClass][$priority][] = $subscriber;
                krsort($this->index[$eventClass]);
            }
        } else {
            $this->legacySubscribers[] = $subscriber;
        }
    }
    
    /**
     * Event unsubscription
     * @param SubscriberInterface $subscriber
     */
    public function unSubscribe(SubscriberInterface $subscriber): void
    {
        $key = array_search($subscriber, $this->subscribers);
        if ( false !== $key ) {
            unset($this->subscribers[$key]);
        }
        
        if ( $subscriber instanceof IndexedSubscriberInterface ) {
            foreach ( $subscriber::getSubscribedEvents() as $eventClass ) {
                if ( !isset($this->index[$eventClass]) ) {
                    continue;
                }
                foreach ( $this->index[$eventClass] as $priority => $bucket ) {
                    $idx = array_search($subscriber, $bucket, true);
                    if ( false !== $idx ) {
                        unset($this->index[$eventClass][$priority][$idx]);
                    }
                }
            }
        } else {
            $legacyKey = array_search($subscriber, $this->legacySubscribers, true);
            if ( false !== $legacyKey ) {
                unset($this->legacySubscribers[$legacyKey]);
            }
        }
    }
    
    /**
     * Publishes one or more events to all interested subscribers.
     *
     * Indexed subscribers (IndexedSubscriberInterface) are dispatched via the event index
     * in priority order (the highest first) without calling isSubscribedTo().
     * Non-indexed subscribers fall back to the legacy linear scan.
     *
     * Async subscribers are dispatched via AsyncDispatcherInterface if available.
     *
     * @param EventInterface ...$events
     */
    public function publish(EventInterface ...$events): self
    {
        foreach ( $events as $event ) {
            $eventClass = $event::class;
            
            // Fast path: indexed subscribers, already sorted by priority at subscribe() time.
            if ( isset($this->index[$eventClass]) ) {
                foreach ( $this->index[$eventClass] as $bucket ) {
                    foreach ( $bucket as $subscriber ) {
                        $this->dispatch($subscriber, $event);
                    }
                }
            }
            
            // Fallback: linear scan for non-indexed (legacy) subscribers.
            // Only iterates the small set of legacy subscribers, not the full list.
            foreach ( $this->legacySubscribers as $subscriber ) {
                if ( $subscriber->isSubscribedTo($event) ) {
                    $this->dispatch($subscriber, $event);
                }
            }
        }
        
        return static::$instance;
    }
    
    /**
     * Dispatches a single subscriber, routing to async if applicable.
     */
    private function dispatch(SubscriberInterface $subscriber, EventInterface $event): void
    {
        if ( null !== $this->asyncDispatcher && $subscriber instanceof AsyncSubscriberInterface ) {
            $this->asyncDispatcher->dispatch($event, $subscriber);
        } else {
            $subscriber->handle($event);
        }
    }
    
    /**
     * Returns all subscribers
     * @return array
     */
    public function getSubscribers(): array
    {
        return $this->subscribers;
    }
    
    /**
     * Returns count of non-indexed (legacy) subscribers. For debugging only.
     * @return int
     */
    public function getLegacySubscribersCount(): int
    {
        return count($this->legacySubscribers);
    }
    
    /**
     * Returns class names of non-indexed (legacy) subscribers. For debugging only.
     * @return array<string, int>
     */
    public function getLegacySubscriberClasses(): array
    {
        $classes = [];
        foreach ( $this->legacySubscribers as $subscriber ) {
            $class = $subscriber::class;
            $classes[$class] = ( $classes[$class] ?? 0 ) + 1;
        }
        ksort($classes);
        return $classes;
    }
    
    /**
     * Returns all subscribers for the given event
     * @param EventInterface $event
     * @return SubscriberInterface[]
     */
    public function getEventSubscribers(EventInterface $event): array
    {
        $subscribers = [];
        foreach ( $this->subscribers as $subscriber ) {
            if ( $subscriber->isSubscribedTo($event) ) {
                $subscribers[] = $subscriber;
            }
        }
        return $subscribers;
    }
    
    /**
     * Returns only subscribers that handle the given event
     * @param EventInterface $event
     * @return SubscriberInterface[]
     */
    public function getEventHandledSubscribers(EventInterface $event): array
    {
        $handledSubscribers = [];
        $processedSubscribers = [];
        
        do {
            $newSubscribers = array_diff($this->subscribers, $processedSubscribers);
            
            foreach ( $newSubscribers as $subscriber ) {
                if ( $subscriber->isHandled($event) ) {
                    $handledSubscribers[] = $subscriber;
                    //$subscriber->handle($event);
                }
                $processedSubscribers[] = $subscriber;
            }
        } while ( count($processedSubscribers) < count($this->subscribers) );
        
        return $handledSubscribers;
    }
}