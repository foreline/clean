<?php
declare(strict_types=1);

namespace Domain\Aggregate;

/**
 * Marker interface for entities that can hold a reference to a SourceInterface.
 */
interface SourceHolderInterface
{
    /**
     * Designated source registry reference. Generator consumers expose a mapped
     * property named source and narrow this return type to its concrete class.
     *
     * @return SourceInterface|null
     */
    public function getSource(): ?SourceInterface;

    public function isPrimary(): bool;
    
    public function setIsPrimary(bool $isPrimary): self;

    /**
     * Получить собственный шаблон URL связи (пустая строка — используется шаблон Source)
     * @return string
     */
    public function getUrl(): string;

    /**
     * Получить resolved URL записи во внешней системе:
     * собственный шаблон URL с подстановкой sourceId, если задан,
     * иначе — шаблон из связанного Source, иначе — пустая строка.
     * @param string|null $sourceId
     * @return string
     */
    public function getSourceUrl(?string $sourceId = null): string;
}
