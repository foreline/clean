<?php
declare(strict_types=1);

namespace Domain\Event;

use DateTimeImmutable;
use Domain\User\Aggregate\User;
use Domain\User\Service\GetCurrentUser;
use Exception;

/**
 * Domain Event
 */
class Event implements EventInterface
{
    private DateTimeImmutable $occurredOn;
    private ?User $user = null;

    /**
     *
     */
    public function __construct()
    {
        $this->occurredOn = new DateTimeImmutable();
        try {
            $this->user = ( new GetCurrentUser() )->get();
        } catch (Exception) {
        }
    }

    /**
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
    
    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }
}