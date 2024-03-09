<?php
    declare(strict_types=1);
    
    namespace Domain\ValueObject;

    /**
     * Though Value-Objects are immutable and ID agnostic,
     * we need to store values somewhere.
     */
    interface PersistedValueObjectInterface extends ValueObjectInterface
    {
        
    }