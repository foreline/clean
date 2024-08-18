<?php
declare(strict_types=1);

namespace Domain\Entity;

/**
 * Slug Interface
 */
interface SlugInterface
{
    /**
     * Gets Entity detail page url
     * @return string
     */
    public function getSlug(): string;

    /**
     * Sets Entity detail page url
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): static;

    /**
     * Gets Entity add/edit page url
     * @return string
     */
    public function getAddSlug(): string;

    /**
     * Sets Entity add/edit page url
     * @param string $addSlug
     * @return $this
     */
    public function setAddSlug(string $addSlug): static;
}