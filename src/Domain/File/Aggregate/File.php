<?php
declare(strict_types=1);

namespace Domain\File\Aggregate;

use DateTimeImmutable;
use Domain\Aggregate\AggregateInterface;

/**
 * File Aggregate
 */
class File implements AggregateInterface, FileInterface
{
    /** @var int|null File ID */
    private ?int $id;
    
    /** @var DateTimeImmutable|null  */
    private ?DateTimeImmutable $dateCreated = null;
    
    /** @var string Module ID */
    private string $moduleId = '';
    
    /** @var int Height */
    private int $height = 0;
    
    /** @var int Width */
    private int $width = 0;
    
    /** @var int File size in bytes */
    private int $size = 0;
    
    /** @var string Content type */
    private string $contentType = '';
    
    /** @var string Subdir */
    private string $subdir = '';
    
    /** @var string File Name */
    private string $fileName = '';
    
    /** @var string File original name */
    private string $originalName = '';
    
    /** @var string File description */
    private string $description = '';
    
    /** @var string Handler ID */
    private string $handlerId = '';
    
    /** @var string External ID */
    private string $extId = '';
    
    // Non persistent fields
    
    /** @var string File Url */
    private string $source = '';
    
    /** @var string File tmp_name while uploading */
    private string $tmpName = '';
    
    /** @var string Absolute path to file on server */
    private string $path = '';
    
    /** @var bool File was deleted */
    private bool $removed = false;
    
    /** @var bool File was uploaded */
    private bool $newlyUploaded = false;
    
    /** @var int Количество */
    private int $aggregatedCount = 1;
    
    /**
     * 
     */
    public function __construct()
    {
    }
    
    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * @param int|null $id
     * @return $this
     */
    public function setId(?int $id): static
    {
        $this->id = $id;
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
     * @return $this
     */
    public function setDateCreated(?DateTimeImmutable $dateCreated): static
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getModuleId(): string
    {
        return $this->moduleId;
    }
    
    /**
     * @param string $moduleId
     * @return $this
     */
    public function setModuleId(string $moduleId): static
    {
        $this->moduleId = $moduleId;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }
    
    /**
     * @param int $height
     * @return $this
     */
    public function setHeight(int $height): static
    {
        $this->height = $height;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }
    
    /**
     * @param int $width
     * @return $this
     */
    public function setWidth(int $width): static
    {
        $this->width = $width;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }
    
    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType(string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSubdir(): string
    {
        return $this->subdir;
    }
    
    /**
     * @param string $subdir
     * @return $this
     */
    public function setSubdir(string $subdir): static
    {
        $this->subdir = $subdir;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getHandlerId(): string
    {
        return $this->handlerId;
    }
    
    /**
     * @param string $handlerId
     * @return $this
     */
    public function setHandlerId(string $handlerId): static
    {
        $this->handlerId = $handlerId;
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
    
    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }
    
    /**
     * @param string $source
     * @return self
     */
    public function setSource(string $source): self
    {
        $this->source = $source;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName(): string
    {
        return !empty($this->getDescription()) ? $this->getDescription() : $this->getFileName();
    }
    
    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }
    
    /**
     * @param string $fileName
     * @return self
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTmpName(): string
    {
        return $this->tmpName;
    }
    
    /**
     * @param string $tmpName
     * @return $this
     */
    public function setTmpName(string $tmpName): self
    {
        $this->tmpName = $tmpName;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getOriginalName(): string
    {
        return $this->originalName;
    }
    
    /**
     * @param string $originalName
     * @return self
     */
    public function setOriginalName(string $originalName): self
    {
        $this->originalName = $originalName;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }
    
    /**
     * @param int $size
     * @return self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
    
    /**
     * @param string $path
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->getPath();
    }
    
    /**
     * @return bool
     */
    public function isRemoved(): bool
    {
        return $this->removed;
    }
    
    /**
     * @param bool $removed
     * @return self
     */
    public function setRemoved(bool $removed): self
    {
        $this->removed = $removed;
        return $this;
    }
    
    /**
     * @return bool
     */
    public function isNewlyUploaded(): bool
    {
        return $this->newlyUploaded;
    }
    
    /**
     * @param bool $newlyUploaded
     * @return self
     */
    public function setNewlyUploaded(bool $newlyUploaded): self
    {
        $this->newlyUploaded = $newlyUploaded;
        return $this;
    }
    
    /**
     * Разрешение файла
     * @return string
     */
    public function getExtension(): string
    {
        return substr($this->getFileName(), strrpos($this->getFileName(), ".") + 1);
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
     */
    public function setAggregatedCount(int $aggregatedCount): void
    {
        $this->aggregatedCount = $aggregatedCount;
    }
    
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
     * Returns md5 hash of file content
     * @return string
     */
    public function getHash(): string
    {
        return md5($this->getContent());
    }
}