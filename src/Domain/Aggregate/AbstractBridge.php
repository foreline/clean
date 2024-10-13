<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use DateTimeImmutable;
use Domain\User\Aggregate\User;

/**
 *
 */
abstract class AbstractBridge
{
    /** @var User|null Инициатор */
    private ?User $createdBy = null;
    
    /** @var DateTimeImmutable|null Дата создания */
    private ?DateTimeImmutable $dateCreated = null;
    
    /** @var User|null Кем изменено */
    private ?User $modifiedBy = null;
    
    /** @var DateTimeImmutable|null Дата изменения */
    private ?DateTimeImmutable $dateModified = null;
    
    /** @var bool Удалено */
    //private bool $deleted = false;
    
    
    /**
     * @return User|null
     */
    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
    
    /**
     * @param User|null $createdBy
     * @return AbstractBridge
     */
    public function setCreatedBy(?User $createdBy): AbstractBridge
    {
        $this->createdBy = $createdBy;
        return $this;
    }
    
    /**
     * @return DateTimeImmutable|null
     */
    public function getDateCreated(): ?DateTimeImmutable
    {
        return $this->dateCreated;
    }
    
    /**
     * @param DateTimeImmutable|null $dateCreated
     * @return AbstractBridge
     */
    public function setDateCreated(?DateTimeImmutable $dateCreated): AbstractBridge
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }
    
    /**
     * @return User|null
     */
    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }
    
    /**
     * @param User|null $modifiedBy
     * @return AbstractBridge
     */
    public function setModifiedBy(?User $modifiedBy): AbstractBridge
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
    
    /**
     * @return DateTimeImmutable|null
     */
    public function getDateModified(): ?DateTimeImmutable
    {
        return $this->dateModified;
    }
    
    /**
     * @param DateTimeImmutable|null $dateModified
     * @return AbstractBridge
     */
    public function setDateModified(?DateTimeImmutable $dateModified): AbstractBridge
    {
        $this->dateModified = $dateModified;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    
    /**
     * @param bool $deleted
     * @return AbstractBridge
     */
    public function setDeleted(bool $deleted): AbstractBridge
    {
        $this->deleted = $deleted;
        return $this;
    }
}