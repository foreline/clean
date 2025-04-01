<?php
declare(strict_types=1);

namespace Domain\File\Aggregate;

/**
 * File interface
 */
interface FileInterface
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