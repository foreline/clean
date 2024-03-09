<?php
    declare(strict_types=1);
    
    namespace Domain\Aggregate;

    use Domain\Entity\EntityInterface;
    use Domain\Entity\ToArrayInterface;

    /**
     * Aggregate Interface
     */
    interface AggregateInterface extends EntityInterface, ToArrayInterface
    {
    
    }