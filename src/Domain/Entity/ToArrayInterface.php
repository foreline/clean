<?php
    declare(strict_types=1);
    
    namespace Domain\Entity;

    /**
     *
     */
    interface ToArrayInterface
    {
        /**
         * Returns array presentation of Entity
         * @param array $fields
         * @return ?array
         */
        public function toArray(array $fields = []): ?array;
    }