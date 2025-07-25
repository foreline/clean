<?php
declare(strict_types=1);

namespace Infrastructure\DI\Bridge;

use Infrastructure\DI\ContainerInterface;
use Infrastructure\DI\ServiceProviderInterface;

/**
 * Symfony Framework Bridge
 * 
 * Integrates Pristine DI services with Symfony's DI container
 * using the single-kernel approach to avoid dual container issues.
 */
class SymfonyBridge implements ServiceProviderInterface
{
    private object $symfonyContainer;
    private array $serviceMap = [];

    /**
     * @param object $symfonyContainer Symfony's container instance
     */
    public function __construct(object $symfonyContainer)
    {
        $this->symfonyContainer = $symfonyContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function register(ContainerInterface $container): void
    {
        // Register Symfony services in Pristine container
        $this->registerSymfonyServices($container);
        
        // Register Pristine services for Symfony access
        $this->registerPristineServices($container);
    }

    /**
     * Map Symfony service to Pristine container
     */
    public function mapService(string $pristineId, string $symfonyId): void
    {
        $this->serviceMap[$pristineId] = $symfonyId;
    }

    /**
     * Register Symfony services in Pristine container
     */
    private function registerSymfonyServices(ContainerInterface $container): void
    {
        foreach ($this->serviceMap as $pristineId => $symfonyId) {
            $container->bind($pristineId, function() use ($symfonyId) {
                return $this->getSymfonyService($symfonyId);
            }, true);
        }

        // Common Symfony services
        $this->registerCommonSymfonyServices($container);
    }

    /**
     * Register common Symfony services
     */
    private function registerCommonSymfonyServices(ContainerInterface $container): void
    {
        // Register Doctrine EntityManager if available
        if ($this->hasSymfonyService('doctrine.orm.entity_manager')) {
            $container->bind('doctrine.entity_manager', function() {
                return $this->getSymfonyService('doctrine.orm.entity_manager');
            }, true);
        }

        // Register Symfony Mailer if available
        if ($this->hasSymfonyService('mailer')) {
            $container->bind('symfony.mailer', function() {
                return $this->getSymfonyService('mailer');
            }, true);
        }

        // Register Symfony Logger if available
        if ($this->hasSymfonyService('logger')) {
            $container->bind('symfony.logger', function() {
                return $this->getSymfonyService('logger');
            }, true);
        }
    }

    /**
     * Register Pristine services for Symfony access
     */
    private function registerPristineServices(ContainerInterface $container): void
    {
        // This method can be extended to register Pristine services
        // in Symfony container if needed (bi-directional bridge)
    }

    /**
     * Get service from Symfony container
     */
    private function getSymfonyService(string $id): mixed
    {
        if (method_exists($this->symfonyContainer, 'get')) {
            return $this->symfonyContainer->get($id);
        }

        throw new \RuntimeException("Cannot retrieve service '{$id}' from Symfony container");
    }

    /**
     * Check if Symfony service exists
     */
    private function hasSymfonyService(string $id): bool
    {
        if (method_exists($this->symfonyContainer, 'has')) {
            return $this->symfonyContainer->has($id);
        }

        return false;
    }

    /**
     * Create bridge from Symfony container
     */
    public static function fromSymfonyContainer(object $symfonyContainer): self
    {
        return new self($symfonyContainer);
    }
}
