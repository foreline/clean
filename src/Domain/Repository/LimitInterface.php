<?php
declare(strict_types=1);

namespace Domain\Repository;

/**
 *
 */
interface LimitInterface
{
    public function reset(): self;
}