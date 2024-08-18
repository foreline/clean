<?php
declare(strict_types=1);

namespace Domain\File\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\File\Entity\FileEntity;

/**
 * File Aggregate
 */
class File extends FileEntity implements AggregateInterface
{
    /** @var string  */
    private string $slug = '';
    
    /** @var string  */
    private string $addSlug = '';

    /** @var string Внешний ID */
    private string $extId = '';
    
    /**
     * HTML tag for file download
     * @return string
     */
    public function getDownloadLink(): string
    {
        return
            '<a
                href="' . $this->getSource() . '"
                download="' . $this->getOriginalName() . '"
                target="_blank"
            >' . ($this->getDescription() ?: $this->getOriginalName()) . '</a>';
    }
    
    /**
     * Returns file contents
     * @return string $content
     */
    public function getContent(): string
    {
        return (string)file_get_contents($this->getPath());
    }
    
    /**
     * @return string
     */
    public function getDetailPageUrl(): string
    {
        return $this->getSlug();
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getAddSlug(): string
    {
        return $this->addSlug;
    }

    /**
     * @param string $addSlug
     * @return $this
     */
    public function setAddSlug(string $addSlug): static
    {
        $this->addSlug = $addSlug;
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
     * @return File
     */
    public function setExtId(string $extId): self
    {
        $this->extId = $extId;
        return $this;
    }
    
    /**
     * @param array $fields
     * @return ?array
     */
    public function toArray(array $fields = []): ?array
    {
        return [
            'entityType'    => 'file',
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'source'        => $this->getSource(),
            'originalName'  => $this->getOriginalName(),
            'slug'          => $this->getSlug(),
        ];
    }
}