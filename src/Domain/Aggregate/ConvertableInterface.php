<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * It can be used for tracking entity original ID in case of changing entity repository
 */
interface ConvertableInterface
{
    /**
     * Gets external ID
     * @return string
     */
    public function getExtId(): string;

    /**
     * Sets external ID
     * @param string $extId
     * @return $this
     */
    public function setExtId(string $extId): self;
}