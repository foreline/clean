<?php
declare(strict_types=1);

namespace Domain\User\Aggregate;

/**
 * Group interface
 */
interface GroupInterface
{
    /**
     * @return int|null
     */
    public function getId(): ?int;
    
    /**
     * @param ?int $id
     * @return $this
     */
    public function setId(?int $id): static;
}