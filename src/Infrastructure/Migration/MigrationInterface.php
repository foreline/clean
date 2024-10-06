<?php
declare(strict_types=1);

namespace Infrastructure\Migration;

/**
 * Интерфейс для классов с миграциями
 */
interface MigrationInterface
{
    /**
     * Описание миграции
     * @return string
     */
    public function getDescription(): string;
    
    /**
     * Метод выполняющий миграцию
     * @return void
     */
    public function up(): void;
    
    /**
     * Метод, откатывающий миграцию
     * @return void
     */
    public function down(): void;
}