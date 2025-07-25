<?php
declare(strict_types=1);

namespace Infrastructure\DI;

use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Extended Container Interface for Pristine Framework
 * 
 * Extends PSR-11 with additional methods for service registration
 * and lifecycle management.
 */
interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Bind a service to the container
     * 
     * @param string $abstract Service identifier (interface or class name)
     * @param mixed $concrete Concrete implementation, class name, or factory
     * @param bool $singleton Whether service should be singleton
     */
    public function bind(string $abstract, mixed $concrete, bool $singleton = false): void;

    /**
     * Register a service as singleton
     * 
     * @param string $abstract Service identifier
     * @param mixed $concrete Concrete implementation
     */
    public function singleton(string $abstract, mixed $concrete): void;

    /**
     * Register a service provider
     * 
     * @param ServiceProviderInterface $provider
     */
    public function registerProvider(ServiceProviderInterface $provider): void;

    /**
     * Check if service is registered as singleton
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function isSingleton(string $id): bool;

    /**
     * Clear all registered services (useful for testing)
     */
    public function clear(): void;
}
