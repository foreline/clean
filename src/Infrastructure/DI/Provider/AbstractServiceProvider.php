<?php
declare(strict_types=1);

namespace Infrastructure\DI\Provider;

use Infrastructure\DI\ContainerInterface;
use Infrastructure\DI\ServiceProviderInterface;

/**
 * Abstract Service Provider
 * 
 * Base class for service providers with common functionality
 * and environment-aware registration patterns.
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    protected string $environment;

    public function __construct(string $environment = 'production')
    {
        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function register(ContainerInterface $container): void
    {
        // Register common services
        $this->registerServices($container);

        // Register environment-specific services
        match ($this->environment) {
            'development', 'dev' => $this->registerDevelopmentServices($container),
            'testing', 'test' => $this->registerTestingServices($container),
            'production', 'prod' => $this->registerProductionServices($container),
            default => null,
        };
    }

    /**
     * Register common services (all environments)
     */
    abstract protected function registerServices(ContainerInterface $container): void;

    /**
     * Register development-specific services
     */
    protected function registerDevelopmentServices(ContainerInterface $container): void
    {
        // Override in subclasses if needed
    }

    /**
     * Register testing-specific services
     */
    protected function registerTestingServices(ContainerInterface $container): void
    {
        // Override in subclasses if needed
    }

    /**
     * Register production-specific services
     */
    protected function registerProductionServices(ContainerInterface $container): void
    {
        // Override in subclasses if needed
    }

    /**
     * Get current environment
     */
    protected function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Check if running in development environment
     */
    protected function isDevelopment(): bool
    {
        return in_array($this->environment, ['development', 'dev']);
    }

    /**
     * Check if running in testing environment
     */
    protected function isTesting(): bool
    {
        return in_array($this->environment, ['testing', 'test']);
    }

    /**
     * Check if running in production environment
     */
    protected function isProduction(): bool
    {
        return in_array($this->environment, ['production', 'prod']);
    }
}
