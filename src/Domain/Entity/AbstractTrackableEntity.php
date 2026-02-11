<?php
declare(strict_types=1);

namespace Domain\Entity;

use DateTimeImmutable;
use Domain\User\Aggregate\User;

/**
 * Abstract Entity with tracking fields (created by, modified by, date created, date modified).
 * Use this as a base class for entities that require tracking of creation and modification details.
 */
class AbstractTrackableEntity extends AbstractEntity
{
    /** @var User|null Кем создано */
    private ?User $createdBy = null;
    
    /** @var User|null Кем изменено */
    private ?User $modifiedBy = null;
    
    /** @var DateTimeImmutable|null Дата создания */
    private ?DateTimeImmutable $dateCreated = null;
    
    /** @var DateTimeImmutable|null Дата изменения */
    private ?DateTimeImmutable $dateModified = null;
    
    /**
     * @return ?User
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
    
    /**
     * @param User|null $createdBy
     * @return self
     */
    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }
    
    /**
     * @return ?User
     */
    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }
    
    /**
     * @param User|null $modifiedBy
     * @return self
     */
    public function setModifiedBy(?User $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
    
    /**
     * @return ?DateTimeImmutable
     */
    public function getDateCreated(): ?DateTimeImmutable
    {
        return $this->dateCreated;
    }
    
    /**
     * @param DateTimeImmutable $dateCreated
     * @return self
     */
    public function setDateCreated(DateTimeImmutable $dateCreated): self
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }
    
    /**
     * @return ?DateTimeImmutable
     */
    public function getDateModified(): ?DateTimeImmutable
    {
        return $this->dateModified;
    }
    
    /**
     * @param DateTimeImmutable $dateModified
     * @return self
     */
    public function setDateModified(DateTimeImmutable $dateModified): self
    {
        $this->dateModified = $dateModified;
        return $this;
    }
    
    /**
     * Проверяет создан ли элемент указанным пользователем
     *
     * @param int $userId
     * @return bool
     */
    public function isCreatedBy(int $userId): bool
    {
        return ($this->getCreatedBy() && $userId === $this->getCreatedBy()->getId());
    }
    
    /**
     * Проверяет изменен ли элемент указанным пользователем
     *
     * @param int $userId
     * @return bool
     */
    public function isModifiedBy(int $userId): bool
    {
        return ($this->getModifiedBy() && $userId === $this->getModifiedBy()->getId());
    }
}