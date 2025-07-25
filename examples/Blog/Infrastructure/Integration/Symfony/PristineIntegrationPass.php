<?php
declare(strict_types=1);

namespace App\Infrastructure\Integration\Symfony;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use App\Domain\Post\UseCase\PostManager;
use App\Domain\Post\Repository\PostRepositoryInterface;
use App\Infrastructure\Post\Repository\DatabasePostRepository;

/**
 * Symfony Compiler Pass to register Pristine services
 * NO separate Pristine kernel needed - everything goes into Symfony container
 */
class PristineIntegrationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Register Pristine repository interface
        $container->register(PostRepositoryInterface::class, DatabasePostRepository::class)
            ->setPublic(false);

        // Register Pristine domain manager
        $container->register(PostManager::class)
            ->addArgument($container->getDefinition(PostRepositoryInterface::class))
            ->setPublic(true);

        // Auto-tag domain services for easy discovery
        $container->registerForAutoconfiguration(PostManager::class)
            ->addTag('pristine.domain_service');
    }
}
