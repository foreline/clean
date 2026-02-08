<?php
declare(strict_types=1);

namespace Domain\Entity;

/**
 * @deprecated
 */
interface ToArrayInterface
{
    /**
     * Returns array presentation of Entity
     * @param array $fields
     * @return ?array
     * @deprecated
     */
    public function toArray(array $fields = []): ?array;
}