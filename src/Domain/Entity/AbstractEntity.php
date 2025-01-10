<?php
declare(strict_types=1);

namespace Domain\Entity;

use DateTimeImmutable;
use InvalidArgumentException;
use Webmozart\Assert\Assert;
use Domain\User\Aggregate\User;

/**
 * Abstract Entity
 */
abstract class AbstractEntity
{
    /** @var int|null ID */
    private ?int $id = null;
    
    /** @var User|null Кем создано */
    private ?User $createdBy = null;
    
    /** @var User|null Кем изменено */
    private ?User $modifiedBy = null;
    
    /** @var DateTimeImmutable|null Дата создания */
    private ?DateTimeImmutable $dateCreated = null;
    
    /** @var DateTimeImmutable|null Дата изменения */
    private ?DateTimeImmutable $dateModified = null;
    
    /** @var string Название */
    private string $name = '';

    private string $detailPageUrl = '';
    private string $listUrl = '';
    private string $addUrl = '';
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param ?int $id
     * @return self
     * @throw InvalidArgumentException
     */
    public function setId(?int $id): self
    {
        if ( null === $id ) {
            $this->id = null;
            return $this;
        }
        
        // ID должен быть положительным числом
        Assert::positiveInteger($id, 'ID должен быть положительным');
        
        // ID нельзя менять
        if ( 0 < $this->id && $id !== $this->id ) {
            throw new InvalidArgumentException('ID не может быть изменен');
        }
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     * @throw \InvalidArgumentException
     */
    public function setName(string $name): self
    {
        if ( 255 < mb_strlen($name) ) {
            throw new InvalidArgumentException('Название не может быть более 255 символов');
        }
        $this->name = $name;
        return $this;
    }

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
     * @return string
     */
    public function getDetailPageUrl(): string
    {
        return $this->detailPageUrl;
    }

    /**
     * @param string $detailPageUrl
     * @return self
     */
    public function setDetailPageUrl(string $detailPageUrl): self
    {
        $this->detailPageUrl = $detailPageUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->getDetailPageUrl();
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): static
    {
        $this->detailPageUrl = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getListUrl(): string
    {
        return $this->listUrl;
    }

    /**
     * @param string $listUrl
     * @return self
     */
    public function setListUrl(string $listUrl): self
    {
        $this->listUrl = $listUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddSlug(): string
    {
        return $this->addUrl;
    }

    /**
     * @param string $addSlug
     * @return $this
     */
    public function setAddSlug(string $addSlug): static
    {
        $this->addUrl = $addSlug;
        return $this;
    }

    /**
     * @return string
     * @deprecated
     */
    public function getAddUrl(): string
    {
        return $this->addUrl;
    }

    /**
     * @param string $addUrl
     * @return self
     * @deprecated
     */
    public function setAddUrl(string $addUrl): self
    {
        $this->addUrl = $addUrl;
        return $this;
    }

    /**
     * Проверяет создан ли элемент указанным пользователем
     * @param int $userId
     * @return bool
     */
    public function isCreatedBy(int $userId): bool
    {
        return ($this->getCreatedBy() && $userId === $this->getCreatedBy()->getId());
    }

    /**
     * Проверяет изменен ли элемент указанным пользователем
     * @param int $userId
     * @return bool
     */
    public function isModifiedBy(int $userId): bool
    {
        return ($this->getModifiedBy() && $userId === $this->getModifiedBy()->getId());
    }
}