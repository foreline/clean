<?php
declare(strict_types=1);

namespace App\Infrastructure\DI\Providers;

use App\Infrastructure\DI\ServiceProviderInterface;
use App\Infrastructure\DI\Container;

// Domain
use App\Domain\Post\UseCase\PostManager;
use App\Domain\Post\UseCase\CreatePost;
use App\Domain\Post\UseCase\GetPost;
use App\Domain\Post\UseCase\UpdatePost;

// Repository Interfaces
use App\Domain\Post\Repository\PostRepositoryInterface;

// Infrastructure Implementations
use App\Infrastructure\Post\Repository\DatabasePostRepository;

/**
 * Service Provider for Domain Layer Services
 */
class DomainServiceProvider implements ServiceProviderInterface
{
    public function register(\Psr\Container\ContainerInterface $container): void
    {
        if (!$container instanceof Container) {
            throw new \InvalidArgumentException('Container must be an instance of App\Infrastructure\DI\Container');
        }

        // Register Repository Implementations
        $container->bind(
            PostRepositoryInterface::class,
            DatabasePostRepository::class,
            true // singleton
        );

        // Register Domain Managers
        $container->bind(PostManager::class, function (Container $c) {
            return new PostManager(
                $c->get(PostRepositoryInterface::class)
            );
        }, true);

        // Register Use Cases (only if they have constructors that need DI)
        // These will be auto-resolved by container if they follow convention
        $container->bind(CreatePost::class, CreatePost::class);
        $container->bind(GetPost::class, GetPost::class);
        $container->bind(UpdatePost::class, UpdatePost::class);
    }
}
