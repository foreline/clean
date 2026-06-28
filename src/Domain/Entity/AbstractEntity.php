<?php
declare(strict_types=1);

namespace Domain\Entity;

use InvalidArgumentException;

/**
 * Abstract Entity
 */
abstract class AbstractEntity
{
    /** @var int|null ID */
    private ?int $id = null;
    
    /** @var string Название */
    private string $name = '';
    
    /** @var int Количество. Используется при группировке элементов */
    private int $aggregatedCount = 1;
    
    /** @var string Внешний ID */
    private string $extId = '';
    
    /**
     * @var string Ссылка на элемент
     *
     * @deprecated
     */
    private string $detailPageUrl = '';
    
    /** @var string Ссылка на элемент */
    private string $slug = '';
    
    /**
     * @var string
     *
     * @deprecated
     */
    private string $listUrl = '';
    
    /**
     * @var string
     *
     * @deprecated
     */
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
        
        if ( 0 > $id ) {
            throw new InvalidArgumentException('ID должен быть положительным');
        }
        
        // ID cannot be changed
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
     * @throw InvalidArgumentException
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
     * @return string
     * @deprecated
     */
    public function getDetailPageUrl(): string
    {
        return $this->detailPageUrl;
    }
    
    /**
     * @param string $detailPageUrl
     * @return self
     * @deprecated
     */
    public function setDetailPageUrl(string $detailPageUrl): self
    {
        $this->detailPageUrl = $detailPageUrl;
        return $this;
    }
    
    /**
     * @return string
     * @deprecated
     */
    public function getSlug(): string
    {
        return $this->slug ?: $this->getDetailPageUrl();
    }
    
    /**
     * @param string $slug
     * @return $this
     * @deprecated
     */
    public function setSlug(string $slug): static
    {
        $this->detailPageUrl = $slug;
        return $this;
    }
    
    /**
     * @return string
     * @deprecated
     */
    public function getListUrl(): string
    {
        return $this->listUrl;
    }
    
    /**
     * @param string $listUrl
     * @return self
     * @deprecated
     */
    public function setListUrl(string $listUrl): self
    {
        $this->listUrl = $listUrl;
        return $this;
    }
    
    /**
     * @return string
     * @deprecated
     */
    public function getAddSlug(): string
    {
        return $this->addUrl;
    }
    
    /**
     * @param string $addSlug
     * @return $this
     * @deprecated
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
     * @return int
     */
    public function getAggregatedCount(): int
    {
        return $this->aggregatedCount;
    }
    
    /**
     * @param int $aggregatedCount
     * @return AbstractEntity
     */
    public function setAggregatedCount(int $aggregatedCount): AbstractEntity
    {
        $this->aggregatedCount = $aggregatedCount;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getExtId(): string
    {
        return $this->extId;
    }
    
    /**
     * @param string $extId
     * @return $this
     */
    public function setExtId(string $extId): static
    {
        $this->extId = $extId;
        return $this;
    }
}