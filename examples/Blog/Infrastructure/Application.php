<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Infrastructure\DI\Container;
use App\Infrastructure\DI\Providers\DomainServiceProvider;
use Psr\Container\ContainerInterface;

/**
 * Application Bootstrap
 * Responsible for setting up the DI container and registering services
 */
class Application
{
    private ContainerInterface $container;

    public function __construct()
    {
        $this->container = $this->buildContainer();
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Build and configure the DI container
     */
    private function buildContainer(): ContainerInterface
    {
        $container = new Container();

        // Register service providers
        $this->registerServiceProviders($container);

        return $container;
    }

    /**
     * Register all service providers
     */
    private function registerServiceProviders(Container $container): void
    {
        $providers = [
            new DomainServiceProvider(),
        ];

        foreach ($providers as $provider) {
            $provider->register($container);
        }
    }

    /**
     * Get a service from the container
     */
    public function get(string $id): mixed
    {
        return $this->container->get($id);
    }
}
