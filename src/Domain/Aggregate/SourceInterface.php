<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * Интерфейс источника данных.
 * Маркер и контракт для всех Source-сущностей во всех контекстах.
 */
interface SourceInterface
{
    /**
     * Получить шаблон URL источника
     * @return string
     */
    public function getUrl(): string;

    /**
     * Установить шаблон URL источника
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): static;

    /**
     * Получить URL для конкретной сущности по её sourceId
     * @param string $sourceId
     * @return string
     */
    public function getSourceUrl(string $sourceId): string;
}
