<?php
declare(strict_types=1);

namespace Domain\Repository;

use Domain\Service\ServiceInterface;

class Group implements GroupInterface
{
    /** @var string[] */
    private array $group = [];
    
    private ?ServiceInterface $service;
    
    /**
     * @param ServiceInterface|null $service
     */
    public function __construct(?ServiceInterface $service = null)
    {
        $this->service = $service;
    }
    
    /**
     * @param array $group
     * @return Group
     */
    public function set(array $group): self
    {
        $this->group = $group;
        return $this;
    }
    
    /**
     * @param string $groupField
     * @return $this
     */
    public function add(string $groupField): self
    {
        $this->group[] = $groupField;
        return $this;
    }
    
    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->group;
    }
    
    /**
     * @return GroupInterface
     */
    public function reset(): GroupInterface
    {
        $this->group = [];
        return $this;
    }
    
    /**
     * @return ServiceInterface|null
     */
    public function endGroup(): ?ServiceInterface
    {
        return $this->service;
    }
}