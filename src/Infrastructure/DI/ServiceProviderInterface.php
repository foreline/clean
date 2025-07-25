<?php
declare(strict_types=1);

namespace Infrastructure\DI;

/**
 * Service Provider Interface
 * 
 * Service providers are used to organize service registration
 * and dependency configuration in a modular way.
 */
interface ServiceProviderInterface
{
    /**
     * Register services in the container
     * 
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container): void;
}
