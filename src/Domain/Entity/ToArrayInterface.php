<?php
    declare(strict_types=1);
    
    namespace Domain\Entity;

    /**
     *
     */
    interface ToArrayInterface
    {
        /**
         * Преобразует сущность в массив
         * @param array $fields
         * @return ?array
         */
        public function toArray(array $fields = []): ?array;
    }