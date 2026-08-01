<?php
declare(strict_types=1);

namespace Tests\Domain\Aggregate;

use Domain\Aggregate\SourceInterface;
use Domain\Aggregate\SourceUrlTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for SourceUrlTrait URL resolution
 *
 * @covers \Domain\Aggregate\SourceUrlTrait
 */
class SourceUrlTraitTest extends TestCase
{
    /**
     * @param string $url
     * @param string $sourceId
     * @param SourceInterface|null $source
     * @return object
     */
    private function makeHolder(string $url = '', string $sourceId = '', ?SourceInterface $source = null): object
    {
        return new class($url, $sourceId, $source) {
            use SourceUrlTrait;

            public function __construct(
                private string $url,
                private string $sourceId,
                private ?SourceInterface $source,
            ) {
            }

            public function getUrl(): string
            {
                return $this->url;
            }

            public function getSourceId(): string
            {
                return $this->sourceId;
            }

            public function getSource(): ?SourceInterface
            {
                return $this->source;
            }
        };
    }

    /**
     * @param string $url
     * @return SourceInterface
     */
    private function makeSource(string $url): SourceInterface
    {
        return new class($url) implements SourceInterface {
            public function __construct(private string $url)
            {
            }

            public function getUrl(): string
            {
                return $this->url;
            }

            public function setUrl(string $url): static
            {
                $this->url = $url;
                return $this;
            }

            public function getSourceUrl(string $sourceId): string
            {
                if ( '' === $this->url || '' === $sourceId ) {
                    return '';
                }

                return str_replace('{sourceId}', $sourceId, $this->url);
            }
        };
    }

    /**
     * @return void
     */
    public function testOwnUrlTemplateWins(): void
    {
        $holder = $this->makeHolder(
            url: 'https://custom/{sourceId}',
            sourceId: '42',
            source: $this->makeSource('https://example.com/view?id={sourceId}'),
        );

        $this->assertSame('https://custom/42', $holder->getSourceUrl());
    }

    /**
     * @return void
     */
    public function testFallsBackToSourceTemplate(): void
    {
        $holder = $this->makeHolder(
            sourceId: '42',
            source: $this->makeSource('https://example.com/view?id={sourceId}'),
        );

        $this->assertSame('https://example.com/view?id=42', $holder->getSourceUrl());
    }

    /**
     * @return void
     */
    public function testExplicitSourceIdArgument(): void
    {
        $holder = $this->makeHolder(
            url: 'https://custom/{sourceId}',
            sourceId: '42',
        );

        $this->assertSame('https://custom/7', $holder->getSourceUrl('7'));
    }

    /**
     * @return void
     */
    public function testReturnsEmptyWhenNoTemplates(): void
    {
        $this->assertSame('', $this->makeHolder(sourceId: '42')->getSourceUrl());
    }

    /**
     * @return void
     */
    public function testReturnsEmptyWhenSourceIdEmpty(): void
    {
        $holder = $this->makeHolder(url: 'https://custom/{sourceId}');

        $this->assertSame('', $holder->getSourceUrl());
    }
}
