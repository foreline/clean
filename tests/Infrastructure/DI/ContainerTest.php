<?php
declare(strict_types=1);

namespace Tests\Infrastructure\DI;

use PHPUnit\Framework\TestCase;
use Infrastructure\DI\Container;
use Infrastructure\DI\Exception\ContainerException;
use Infrastructure\DI\Exception\NotFoundException;

class ContainerTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function testCanBindAndResolveService(): void
    {
        $this->container->bind('test', 'stdClass');
        
        $this->assertTrue($this->container->has('test'));
        $this->assertInstanceOf('stdClass', $this->container->get('test'));
    }

    public function testCanBindInterfaceToImplementation(): void
    {
        $this->container->bind(TestInterface::class, TestImplementation::class);
        
        $service = $this->container->get(TestInterface::class);
        $this->assertInstanceOf(TestImplementation::class, $service);
    }

    public function testCanBindFactory(): void
    {
        $this->container->bind('factory_test', function() {
            return new TestClass('factory_value');
        });
        
        $service = $this->container->get('factory_test');
        $this->assertInstanceOf(TestClass::class, $service);
        $this->assertEquals('factory_value', $service->getValue());
    }

    public function testSingletonBehavior(): void
    {
        $this->container->singleton('singleton_test', TestClass::class);
        
        $instance1 = $this->container->get('singleton_test');
        $instance2 = $this->container->get('singleton_test');
        
        $this->assertSame($instance1, $instance2);
        $this->assertTrue($this->container->isSingleton('singleton_test'));
    }

    public function testTransientBehavior(): void
    {
        $this->container->bind('transient_test', TestClass::class, false);
        
        $instance1 = $this->container->get('transient_test');
        $instance2 = $this->container->get('transient_test');
        
        $this->assertNotSame($instance1, $instance2);
        $this->assertFalse($this->container->isSingleton('transient_test'));
    }

    public function testAutowiringWithDependencies(): void
    {
        $this->container->bind(TestDependency::class, TestDependency::class);
        
        $service = $this->container->get(TestClassWithDependency::class);
        
        $this->assertInstanceOf(TestClassWithDependency::class, $service);
        $this->assertInstanceOf(TestDependency::class, $service->getDependency());
    }

    public function testThrowsNotFoundExceptionForUnregisteredService(): void
    {
        $this->expectException(NotFoundException::class);
        $this->container->get('non_existent_service');
    }

    public function testThrowsContainerExceptionForCircularDependency(): void
    {
        $this->container->bind('circular_a', function($container) {
            return new CircularA($container->get('circular_b'));
        });
        
        $this->container->bind('circular_b', function($container) {
            return new CircularB($container->get('circular_a'));
        });
        
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Circular dependency detected');
        
        $this->container->get('circular_a');
    }

    public function testCanClearContainer(): void
    {
        $this->container->bind('test', 'stdClass');
        $this->container->singleton('singleton_test', 'stdClass');
        
        $this->assertTrue($this->container->has('test'));
        $this->assertTrue($this->container->has('singleton_test'));
        
        $this->container->clear();
        
        $this->assertFalse($this->container->has('test'));
        $this->assertFalse($this->container->has('singleton_test'));
    }

    public function testCanRegisterServiceProvider(): void
    {
        $provider = new TestServiceProvider();
        $this->container->registerProvider($provider);
        
        $this->assertTrue($this->container->has('provider_service'));
        $this->assertEquals('provider_value', $this->container->get('provider_service'));
    }

    public function testFactoryReceivesContainer(): void
    {
        $this->container->bind('container_test', function($container) {
            $this->assertInstanceOf(Container::class, $container);
            return 'success';
        });
        
        $result = $this->container->get('container_test');
        $this->assertEquals('success', $result);
    }

    public function testCanResolveClassWithoutBinding(): void
    {
        $service = $this->container->get(TestClass::class);
        $this->assertInstanceOf(TestClass::class, $service);
    }

    public function testHandlesDefaultParameterValues(): void
    {
        $service = $this->container->get(TestClassWithDefault::class);
        $this->assertInstanceOf(TestClassWithDefault::class, $service);
        $this->assertEquals('default', $service->getValue());
    }
}

// Test classes
interface TestInterface {}

class TestImplementation implements TestInterface {}

class TestClass
{
    private string $value;
    
    public function __construct(string $value = 'default')
    {
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
}

class TestDependency {}

class TestClassWithDependency
{
    private TestDependency $dependency;
    
    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }
    
    public function getDependency(): TestDependency
    {
        return $this->dependency;
    }
}

class TestClassWithDefault
{
    private string $value;
    
    public function __construct(string $value = 'default')
    {
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
}

class CircularA
{
    public function __construct(CircularB $b) {}
}

class CircularB
{
    public function __construct(CircularA $a) {}
}

class TestServiceProvider implements \Infrastructure\DI\ServiceProviderInterface
{
    public function register(\Infrastructure\DI\ContainerInterface $container): void
    {
        $container->bind('provider_service', 'provider_value');
    }
}
