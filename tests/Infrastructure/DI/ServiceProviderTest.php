<?php
declare(strict_types=1);

namespace Tests\Infrastructure\DI;

use PHPUnit\Framework\TestCase;
use Infrastructure\DI\Container;
use Infrastructure\DI\Provider\AbstractServiceProvider;
use Infrastructure\DI\ContainerInterface;

class ServiceProviderTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testEnvironmentBasedRegistration(): void
    {
        $provider = new TestEnvironmentProvider('development');
        $this->container->registerProvider($provider);
        
        $this->assertEquals('dev_implementation', $this->container->get('test_service'));
    }

    public function testProductionEnvironment(): void
    {
        $provider = new TestEnvironmentProvider('production');
        $this->container->registerProvider($provider);
        
        $this->assertEquals('prod_implementation', $this->container->get('test_service'));
    }

    public function testTestingEnvironment(): void
    {
        $provider = new TestEnvironmentProvider('testing');
        $this->container->registerProvider($provider);
        
        $this->assertEquals('test_implementation', $this->container->get('test_service'));
    }

    public function testEnvironmentDetection(): void
    {
        $provider = new TestEnvironmentProvider('development');
        
        $this->assertTrue($provider->isDevelopment());
        $this->assertFalse($provider->isProduction());
        $this->assertFalse($provider->isTesting());
    }

    public function testCommonServicesRegistered(): void
    {
        $provider = new TestEnvironmentProvider('production');
        $this->container->registerProvider($provider);
        
        $this->assertTrue($this->container->has('common_service'));
        $this->assertEquals('common_value', $this->container->get('common_service'));
    }
}

class TestEnvironmentProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        $container->bind('common_service', 'common_value');
    }

    protected function registerDevelopmentServices(ContainerInterface $container): void
    {
        $container->bind('test_service', 'dev_implementation');
    }

    protected function registerTestingServices(ContainerInterface $container): void
    {
        $container->bind('test_service', 'test_implementation');
    }

    protected function registerProductionServices(ContainerInterface $container): void
    {
        $container->bind('test_service', 'prod_implementation');
    }

    // Expose protected methods for testing
    public function isDevelopment(): bool
    {
        return parent::isDevelopment();
    }

    public function isProduction(): bool
    {
        return parent::isProduction();
    }

    public function isTesting(): bool
    {
        return parent::isTesting();
    }
}
