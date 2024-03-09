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
        private string $slug = '';
        private string $addSlug = '';
        
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
         * Returns file content
         * @return string $content
         */
        public function getContent(): string
        {
            return (string)file_get_contents($this->getPath());
        }
    
        /**
         * @return string
         */
        public function getSlug(): string
        {
            return $this->slug;
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
         * @param array $fields
         * @return ?array
         */
        public function toArray(array $fields = []): ?array
        {
            return [
                'id'    => $this->getId(),
                'name'  => $this->getName(),
                'source'    => $this->getSource(),
                'originalName'  => $this->getOriginalName(),
                'slug'          => $this->getSlug(),
            ];
        }
    }