<?php
declare(strict_types=1);

namespace Domain\Aggregate;

use DateTimeImmutable;
use Domain\User\Aggregate\UserInterface;

/**
 * Bridge entities are used for maintaining relations between entities
 */
abstract class AbstractBridge
{
    /** @var UserInterface|null Инициатор */
    private ?UserInterface $createdBy = null;
    
    /** @var DateTimeImmutable|null Дата создания */
    private ?DateTimeImmutable $dateCreated = null;
    
    /** @var UserInterface|null Кем изменено */
    private ?UserInterface $modifiedBy = null;
    
    /** @var DateTimeImmutable|null Дата изменения */
    private ?DateTimeImmutable $dateModified = null;
    
    // @var bool Удалено
    //private bool $deleted = false;
    
    /**
     * @return UserInterface|null
     */
    public function getCreatedBy(): ?UserInterface
    {
        return $this->createdBy;
    }
    
    /**
     * @param UserInterface|null $createdBy
     * @return AbstractBridge
     */
    public function setCreatedBy(?UserInterface $createdBy): AbstractBridge
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
     * @return UserInterface|null
     */
    public function getModifiedBy(): ?UserInterface
    {
        return $this->modifiedBy;
    }
    
    /**
     * @param UserInterface|null $modifiedBy
     * @return AbstractBridge
     */
    public function setModifiedBy(?UserInterface $modifiedBy): AbstractBridge
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
    /*public function isDeleted(): bool
    {
        return $this->deleted;
    }*/
    
    /**
     * @param bool $deleted
     * @return AbstractBridge
     */
    /*public function setDeleted(bool $deleted): AbstractBridge
    {
        $this->deleted = $deleted;
        return $this;
    }*/
}