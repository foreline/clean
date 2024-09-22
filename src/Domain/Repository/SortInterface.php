<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
interface SortInterface
{
    public function reset(): self;
}