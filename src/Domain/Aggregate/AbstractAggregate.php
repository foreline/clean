<?php
    declare(strict_types=1);
    
    namespace Domain\Aggregate;
    
    use Domain\Entity\AbstractEntity;

    /**
     * Abstract aggregate for inheritance
     */
    abstract class AbstractAggregate extends AbstractEntity implements AggregateInterface
    {
    
        /**
         * @param array $fields
         * @return array|null
         */
        abstract public function toArray(array $fields = []): ?array;
        
    }