<?php

declare(strict_types=1);

namespace Domain\ValueObject;

/**
 * Interface that defines a Value Object (VO) whose value type is an array.
 */
interface ArrayValueObjectInterface extends ValueObjectInterface
{
    /**
     * @param array $value
     */
    public function __construct(array $value);
    
    /**
     * @return string
     */
    public function toJson(): string;
    
    /**
     * @return array
     */
    public function fromJson(): array;
    
    /**
     * @return string[]
     */
    public static function map(): array;
}