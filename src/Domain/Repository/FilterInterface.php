<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
interface FilterInterface
{
    public function reset(): self;
}