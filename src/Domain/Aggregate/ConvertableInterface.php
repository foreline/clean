<?php
    declare(strict_types=1);
    
    namespace Domain\Aggregate;

    /**
     * Интерфейс для конвертирования репозиториев агрегатов
     */
    interface ConvertableInterface
    {
        /**
         * Получение внешнего ID
         * @return string
         */
        public function getExtId(): string;
    
        /**
         * Задание внешнего ID
         * @param string $extId
         * @return $this
         */
        public function setExtId(string $extId): self;
    }