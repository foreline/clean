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
     * Get JSON representation
     *
     * @return string
     */
    public function toJson(): string;
    
    /**
     * Create ValueObject from json string.
     *
     * @param string $json
     * @return ArrayValueObjectInterface
     */
    public static function fromJson(string $json): self;
    
    /**
     * @return string[]
     */
    public static function map(): array;
}