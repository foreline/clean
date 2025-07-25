<?php
declare(strict_types=1);

namespace App\Infrastructure\Integration\Symfony;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Symfony Bundle for Pristine Framework Integration
 * Registers Pristine services directly in Symfony container
 */
class PristineBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        
        // Add compiler pass to register Pristine services
        $container->addCompilerPass(new PristineIntegrationPass());
    }

    public function getPath(): string
    {
        return dirname(__DIR__, 3);
    }
}
