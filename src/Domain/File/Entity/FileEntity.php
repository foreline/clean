<?php
    declare(strict_types=1);
    
    namespace Domain\File\Entity;
    
    /**
     * File Entity
     */
    class FileEntity
    {
        /** @var int|null File ID */
        private ?int $id;
        
        /** @var string File Url */
        private string $source;
        
        /** @var string File original name */
        private string $originalName;
        
        /** @var string File Name */
        private string $fileName;
        
        /** @var string File tmp_name while uploading */
        private string $tmpName = '';
        
        /** @var string File description */
        private string $description;
        
        /** @var string Absolute path to file on server */
        private string $path;
        
        /** @var int File size in bytes */
        private int $size;
        
        /** @var bool File was deleted */
        private bool $removed = false;
        
        /** @var bool File was uploaded */
        private bool $newlyUploaded = false;
        
        /**
         * @param int|null $id
         * @param string $source
         * @param string $fileName
         * @param string $originalName
         * @param string $description
         * @param int $size
         */
        public function __construct(int $id = null, string $source = '', string $fileName = '', string $originalName = '', string $description = '', int $size = 0)
        {
            $this->id = $id;
            $this->source = $source;
            $this->fileName = $fileName;
            $this->originalName = $originalName;
            $this->description = $description;
            $this->size = $size;
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
         * @return self
         */
        public function setId(?int $id): self
        {
            $this->id = $id;
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
        
    }