<?php
declare(strict_types=1);

namespace Domain\Event;

/**
 * Event subscription and publication
 */
class Publisher
{
    /** @var Subscriber[] */
    private array $subscribers;
    private static ?self $instance = null;
    
    /**
     *
     */
    private function __construct()
    {
        $this->subscribers = [];
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
     * Event subscription
     * @param Subscriber $subscriber
     */
    public function subscribe(SubscriberInterface $subscriber): void
    {
        if ( !in_array($subscriber, $this->subscribers, false)) {
            $this->subscribers[] = $subscriber;
        }
    }
    
    /**
     * Event unsubscription
     * @param Subscriber $subscriber
     */
    public function unSubscribe(SubscriberInterface $subscriber): void
    {
        unset($this->subscribers[array_search($subscriber, $this->subscribers)]);
    }
    
    /**
     * The publish method checks all possible subscribers to see if they are interested in the published domain event.
     * If so, the subscriber's handle method is called.
     * @param Event ...$events
     */
    public function publish(EventInterface ... $events): self
    {
        foreach ( $this->subscribers as $subscriber ) {
            foreach ( $events as $event ) {
                if ( $subscriber->isSubscribedTo($event) ) {
                    $subscriber->handle($event);
                }
            }
        }
        
        return static::$instance;
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