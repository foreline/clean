<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

use Domain\Aggregate\AggregateInterface;
use Domain\User\Entity\GroupEntity;

/**
 * Group aggregate
 */
class Group extends GroupEntity implements AggregateInterface, GroupInterface
{
    /** @var string Внешний ID */
    private string $extId = '';
    
    /** @var string  */
    private string $slug = '';
    
    /** @var string  */
    private string $addSlug = '';

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
    public function setExtId(string $extId): self
    {
        $this->extId = $extId;
        return $this;
    }
    
    /**
     * Group detail page url
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
     * Gets Entity add/edit page url
     * @return string
     */
    public function getAddSlug(): string
    {
        return $this->addSlug;
    }

    /**
     * Sets Entity add/edit page url
     * @param string $addSlug
     * @return $this
     */
    public function setAddSlug(string $addSlug): static
    {
        $this->addSlug = $addSlug;
        return $this;
    }

    /**
     * Returns group as array
     * @param array $fields
     * @return ?array
     */
    public function toArray(array $fields = []): ?array
    {
        return [
            'entity_type'   => 'group',
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'description'   => $this->getDescription(),
            'slug'          => $this->getSlug(),
        ];
    }
}