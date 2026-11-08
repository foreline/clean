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
    private ?int $actorId = null;

    /**
     *
     */
    public function __construct(bool $captureUser = true)
    {
        $this->occurredOn = new DateTimeImmutable();
        try {
            $user = ( new GetCurrentUser() )->get();
            $this->actorId = $user?->getId();
            $this->user = $captureUser ? $user : null;
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

    /** Автор компактного события; старые сериализованные события сохраняют getUser(). */
    public function getActorId(): ?int
    {
        return $this->actorId;
    }
}
