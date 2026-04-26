<?php
declare(strict_types=1);

namespace Domain\Enum;

interface EnumInterface
{
    /**
     * Returns the name of the enum value.
     */
    public function name(): string;

    /**
     * Returns the description of the enum value.
     */
    public function description(): string;
}