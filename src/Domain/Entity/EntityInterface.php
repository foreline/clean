<?php
    declare(strict_types=1);
    
    namespace Domain\Entity;

    /**
     * Entity Interface
     */
    interface EntityInterface
    {
        /**
         * @return int|null
         */
        public function getId(): ?int;
        
    }
    