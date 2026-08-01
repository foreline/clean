<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * Каноническая реализация SourceHolderInterface::getSourceUrl().
 *
 * Трейт написан через публичные геттеры использующего класса,
 * поэтому не зависит от конкретных свойств и применим в любом контексте.
 */
trait SourceUrlTrait
{
    /**
     * Получить собственный шаблон URL связи
     * @return string
     */
    abstract public function getUrl(): string;

    /**
     * Получить ID в источнике
     * @return string
     */
    abstract public function getSourceId(): string;

    /**
     * Получить источник данных
     * @return SourceInterface|null
     */
    abstract public function getSource(): ?SourceInterface;

    /**
     * Получить resolved URL записи во внешней системе.
     * Использует собственный шаблон URL, если задан, иначе — шаблон из Source.
     *
     * @param string|null $sourceId
     * @return string
     */
    public function getSourceUrl(?string $sourceId = null): string
    {
        $sourceId = $sourceId ?? $this->getSourceId();
        $url = $this->getUrl();

        if ( '' !== $url && '' !== $sourceId ) {
            return str_replace('{sourceId}', $sourceId, $url);
        }

        $source = $this->getSource();

        if ( $source instanceof SourceInterface ) {
            return $source->getSourceUrl($sourceId);
        }

        return '';
    }
}
