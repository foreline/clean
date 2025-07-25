# Service Providers Guide

Service Providers are the primary way to organize and register services in the Pristine Framework DI container. They provide a clean, modular approach to service registration with environment-aware capabilities.

## 📋 Table of Contents

1. [Introduction](#introduction)
2. [Creating Service Providers](#creating-service-providers)
3. [Environment-Aware Providers](#environment-aware-providers)
4. [Registration Patterns](#registration-patterns)
5. [Framework Integration](#framework-integration)
6. [Testing Service Providers](#testing-service-providers)
7. [Best Practices](#best-practices)

## 🚀 Introduction

Service Providers encapsulate service registration logic, making it:
- **Modular**: Organize services by domain or functionality
- **Environment-Aware**: Different services per environment
- **Testable**: Easy to test service registration
- **Reusable**: Share providers across applications

### Basic Service Provider

```php
use Infrastructure\DI\ServiceProvider\AbstractServiceProvider;
use Infrastructure\DI\ContainerInterface;

class DomainServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Register domain services
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->bind(UserRepositoryInterface::class, DatabaseUserRepository::class);
        $container->singleton(PostManager::class, PostManager::class);
    }
}
```

## 🏗️ Creating Service Providers

### Domain Service Provider

```php
use Infrastructure\DI\ServiceProvider\AbstractServiceProvider;
use Infrastructure\DI\ContainerInterface;

class BlogDomainServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Repositories
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->bind(CategoryRepositoryInterface::class, DatabaseCategoryRepository::class);
        $container->bind(TagRepositoryInterface::class, DatabaseTagRepository::class);
        
        // Domain Services
        $container->singleton(PostManager::class, PostManager::class);
        $container->singleton(CategoryManager::class, CategoryManager::class);
        $container->singleton(PostValidator::class, PostValidator::class);
        
        // Use Cases
        $container->bind(CreatePostUseCase::class, CreatePostUseCase::class);
        $container->bind(UpdatePostUseCase::class, UpdatePostUseCase::class);
        $container->bind(DeletePostUseCase::class, DeletePostUseCase::class);
    }
}
```

### Infrastructure Service Provider

```php
class InfrastructureServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Database
        $container->singleton(PDO::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            return new PDO(
                $config->get('database.dsn'),
                $config->get('database.username'),
                $config->get('database.password'),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        });
        
        // Email Services
        $container->bind(EmailServiceInterface::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            
            if ($config->get('email.driver') === 'smtp') {
                return new SmtpEmailService($config->get('email.smtp'));
            }
            
            return new LogEmailService($container->get(Logger::class));
        });
        
        // Logging
        $container->singleton(Logger::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            return new FileLogger($config->get('logging.path'));
        });
        
        // Cache
        $container->singleton(CacheInterface::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            
            return match($config->get('cache.driver')) {
                'redis' => new RedisCache($config->get('cache.redis')),
                'file' => new FileCache($config->get('cache.file.path')),
                default => new NullCache()
            };
        });
    }
}
```

### Presentation Layer Provider

```php
class PresentationServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Controllers
        $container->bind(PostController::class, PostController::class);
        $container->bind(CategoryController::class, CategoryController::class);
        $container->bind(UserController::class, UserController::class);
        
        // Response Formatters
        $container->bind(JsonResponseFormatter::class, JsonResponseFormatter::class);
        $container->bind(HtmlResponseFormatter::class, HtmlResponseFormatter::class);
        
        // Request Validators
        $container->bind(PostRequestValidator::class, PostRequestValidator::class);
        $container->bind(UserRequestValidator::class, UserRequestValidator::class);
        
        // Middleware
        $container->bind(AuthenticationMiddleware::class, AuthenticationMiddleware::class);
        $container->bind(AuthorizationMiddleware::class, AuthorizationMiddleware::class);
    }
}
```

## 🌍 Environment-Aware Providers

### Development Environment Provider

```php
class DevelopmentServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Development-specific services
        $container->bind(PostRepositoryInterface::class, InMemoryPostRepository::class);
        $container->bind(EmailServiceInterface::class, LogEmailService::class);
        
        // Debug services
        $container->singleton(DebugProfiler::class, DebugProfiler::class);
        $container->singleton(QueryLogger::class, QueryLogger::class);
        
        // Mock external services
        $container->bind(PaymentServiceInterface::class, MockPaymentService::class);
        $container->bind(ImageProcessorInterface::class, MockImageProcessor::class);
    }
}
```

### Production Environment Provider

```php
class ProductionServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Production repositories
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->bind(EmailServiceInterface::class, SmtpEmailService::class);
        
        // Production cache
        $container->singleton(CacheInterface::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            return new RedisCache($config->get('redis'));
        });
        
        // Real external services
        $container->bind(PaymentServiceInterface::class, StripePaymentService::class);
        $container->bind(ImageProcessorInterface::class, CloudImageProcessor::class);
        
        // Performance monitoring
        $container->singleton(PerformanceMonitor::class, PerformanceMonitor::class);
    }
}
```

### Testing Environment Provider

```php
class TestingServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // In-memory repositories for fast tests
        $container->bind(PostRepositoryInterface::class, InMemoryPostRepository::class);
        $container->bind(UserRepositoryInterface::class, InMemoryUserRepository::class);
        
        // Null services for testing
        $container->bind(EmailServiceInterface::class, NullEmailService::class);
        $container->bind(CacheInterface::class, NullCache::class);
        
        // Test doubles
        $container->bind(PaymentServiceInterface::class, FakePaymentService::class);
        $container->bind(NotificationServiceInterface::class, SpyNotificationService::class);
        
        // Test utilities
        $container->singleton(TestDataFactory::class, TestDataFactory::class);
        $container->singleton(DatabaseCleaner::class, DatabaseCleaner::class);
    }
}
```

## 🔧 Registration Patterns

### Factory-Based Registration

```php
class FactoryServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Complex service with multiple dependencies
        $container->bind(PostNotificationService::class, function(ContainerInterface $container) {
            $service = new PostNotificationService();
            
            // Add multiple notification channels
            $service->addChannel($container->get(EmailNotificationChannel::class));
            $service->addChannel($container->get(SlackNotificationChannel::class));
            $service->addChannel($container->get(WebhookNotificationChannel::class));
            
            return $service;
        });
        
        // Configuration-dependent service
        $container->bind(FileStorageService::class, function(ContainerInterface $container) {
            $config = $container->get(Configuration::class);
            
            return new FileStorageService([
                'disk' => $config->get('storage.disk'),
                'path' => $config->get('storage.path'),
                'url' => $config->get('storage.url'),
                'permissions' => $config->get('storage.permissions', 0755)
            ]);
        });
    }
}
```

### Conditional Registration

```php
class ConditionalServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        $config = $container->get(Configuration::class);
        
        // Feature flag based registration
        if ($config->get('features.advanced_search')) {
            $container->bind(SearchServiceInterface::class, ElasticsearchService::class);
        } else {
            $container->bind(SearchServiceInterface::class, DatabaseSearchService::class);
        }
        
        // Environment-based registration
        if ($config->get('app.debug')) {
            $container->singleton(DebugBar::class, DebugBar::class);
            $container->bind(LoggerInterface::class, VerboseLogger::class);
        } else {
            $container->bind(LoggerInterface::class, ProductionLogger::class);
        }
        
        // Extension-based registration
        if (extension_loaded('redis')) {
            $container->bind(CacheInterface::class, RedisCache::class);
        } elseif (extension_loaded('memcached')) {
            $container->bind(CacheInterface::class, MemcachedCache::class);
        } else {
            $container->bind(CacheInterface::class, FileCache::class);
        }
    }
}
```

### Decorator Pattern Registration

```php
class DecoratorServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Base service
        $container->bind('base.post.repository', DatabasePostRepository::class);
        
        // Decorated with caching
        $container->bind('cached.post.repository', function(ContainerInterface $container) {
            return new CachedPostRepository(
                $container->get('base.post.repository'),
                $container->get(CacheInterface::class)
            );
        });
        
        // Decorated with logging
        $container->bind(PostRepositoryInterface::class, function(ContainerInterface $container) {
            return new LoggedPostRepository(
                $container->get('cached.post.repository'),
                $container->get(LoggerInterface::class)
            );
        });
    }
}
```

## 🌉 Framework Integration

### Symfony Bridge Provider

```php
use Infrastructure\DI\Bridge\SymfonyBridge;

class SymfonyIntegrationProvider extends AbstractServiceProvider
{
    private $symfonyContainer;
    
    public function __construct($symfonyContainer)
    {
        $this->symfonyContainer = $symfonyContainer;
    }
    
    protected function registerServices(ContainerInterface $container): void
    {
        // Register Symfony bridge
        $bridge = new SymfonyBridge($this->symfonyContainer);
        $container->registerProvider($bridge);
        
        // Map Symfony services to Pristine interfaces
        $container->bind(LoggerInterface::class, function() {
            return $this->symfonyContainer->get('logger');
        });
        
        $container->bind(CacheInterface::class, function() {
            return $this->symfonyContainer->get('cache.app');
        });
    }
}
```

### Bitrix Bridge Provider

```php
use Infrastructure\DI\Bridge\BitrixBridge;

class BitrixIntegrationProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Register Bitrix bridge with common modules
        $bridge = BitrixBridge::withCommonModules();
        $container->registerProvider($bridge);
        
        // Bitrix-specific services
        $container->bind(BitrixUserRepositoryInterface::class, BitrixUserRepository::class);
        $container->bind(BitrixIBlockRepositoryInterface::class, BitrixIBlockRepository::class);
        
        // Wrap Bitrix globals
        $container->singleton(BitrixGlobalWrapper::class, function() {
            return new BitrixGlobalWrapper();
        });
    }
}
```

## 🧪 Testing Service Providers

### Unit Testing Providers

```php
use PHPUnit\Framework\TestCase;
use Infrastructure\DI\Container;

class DomainServiceProviderTest extends TestCase
{
    private Container $container;
    private DomainServiceProvider $provider;
    
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->provider = new DomainServiceProvider();
        
        // Register dependencies
        $this->container->singleton(Configuration::class, new TestConfiguration());
    }
    
    public function testRegistersAllServices(): void
    {
        $this->provider->register($this->container, 'test');
        
        // Test service registration
        $this->assertTrue($this->container->has(PostRepositoryInterface::class));
        $this->assertTrue($this->container->has(UserRepositoryInterface::class));
        $this->assertTrue($this->container->has(PostManager::class));
        
        // Test singleton registration
        $this->assertTrue($this->container->isSingleton(PostManager::class));
    }
    
    public function testServiceResolution(): void
    {
        $this->provider->register($this->container, 'test');
        
        $postManager = $this->container->get(PostManager::class);
        $this->assertInstanceOf(PostManager::class, $postManager);
        
        // Test same instance for singleton
        $postManager2 = $this->container->get(PostManager::class);
        $this->assertSame($postManager, $postManager2);
    }
    
    public function testEnvironmentSpecificRegistration(): void
    {
        // Test development environment
        $this->provider->register($this->container, 'development');
        $repository = $this->container->get(PostRepositoryInterface::class);
        $this->assertInstanceOf(InMemoryPostRepository::class, $repository);
        
        // Reset container
        $this->container = new Container();
        $this->container->singleton(Configuration::class, new TestConfiguration());
        
        // Test production environment
        $this->provider->register($this->container, 'production');
        $repository = $this->container->get(PostRepositoryInterface::class);
        $this->assertInstanceOf(DatabasePostRepository::class, $repository);
    }
}
```

### Integration Testing

```php
class ServiceProviderIntegrationTest extends TestCase
{
    public function testFullApplicationBootstrap(): void
    {
        $container = new Container();
        
        // Register all providers in order
        $providers = [
            new ConfigurationServiceProvider(),
            new InfrastructureServiceProvider(),
            new DomainServiceProvider(),
            new PresentationServiceProvider(),
        ];
        
        foreach ($providers as $provider) {
            $provider->register($container, 'test');
        }
        
        // Test that complex service graph resolves correctly
        $controller = $container->get(PostController::class);
        $this->assertInstanceOf(PostController::class, $controller);
        
        // Test that circular dependencies are detected
        $this->expectException(CircularDependencyException::class);
        $container->get(CircularServiceA::class);
    }
}
```

## 📚 Best Practices

### 1. Single Responsibility

```php
// ✅ Good - Domain-focused provider
class BlogDomainServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Only blog-related services
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->bind(PostManager::class, PostManager::class);
    }
}

// ❌ Avoid - Mixed concerns
class MixedServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Mixing domain, infrastructure, and presentation concerns
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->bind(PDO::class, function() { /* ... */ });
        $container->bind(PostController::class, PostController::class);
    }
}
```

### 2. Environment Isolation

```php
// ✅ Good - Environment-specific providers
class ProductionServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Only production-specific registrations
        $container->bind(CacheInterface::class, RedisCache::class);
        $container->bind(EmailServiceInterface::class, SmtpEmailService::class);
    }
}

// ✅ Good - Base provider with environment checking
class EmailServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        if ($this->environment === 'production') {
            $container->bind(EmailServiceInterface::class, SmtpEmailService::class);
        } else {
            $container->bind(EmailServiceInterface::class, LogEmailService::class);
        }
    }
}
```

### 3. Provider Ordering

```php
// ✅ Good - Register providers in dependency order
function bootstrapContainer(): ContainerInterface
{
    $container = new Container();
    
    // 1. Configuration first (needed by others)
    (new ConfigurationServiceProvider())->register($container, getEnvironment());
    
    // 2. Infrastructure (databases, external services)
    (new InfrastructureServiceProvider())->register($container, getEnvironment());
    
    // 3. Domain services (business logic)
    (new DomainServiceProvider())->register($container, getEnvironment());
    
    // 4. Presentation layer (controllers, formatters)
    (new PresentationServiceProvider())->register($container, getEnvironment());
    
    return $container;
}
```

### 4. Factory Organization

```php
// ✅ Good - Extract complex factories
class EmailServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        $container->bind(EmailServiceInterface::class, [$this, 'createEmailService']);
    }
    
    private function createEmailService(ContainerInterface $container): EmailServiceInterface
    {
        $config = $container->get(Configuration::class);
        
        return match($config->get('email.driver')) {
            'smtp' => $this->createSmtpService($config),
            'sendmail' => $this->createSendmailService($config),
            'log' => $this->createLogService($container),
            default => throw new InvalidArgumentException('Unsupported email driver')
        };
    }
    
    private function createSmtpService(Configuration $config): EmailServiceInterface
    {
        $service = new SmtpEmailService();
        $service->setHost($config->get('email.smtp.host'));
        $service->setPort($config->get('email.smtp.port'));
        $service->setCredentials(
            $config->get('email.smtp.username'),
            $config->get('email.smtp.password')
        );
        return $service;
    }
}
```

### 5. Provider Composition

```php
// ✅ Good - Composable providers
class BlogApplicationProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        // Compose multiple providers
        $providers = [
            new BlogDomainServiceProvider(),
            new BlogInfrastructureServiceProvider(),
            new BlogPresentationServiceProvider(),
        ];
        
        foreach ($providers as $provider) {
            $provider->register($container, $this->environment);
        }
    }
}
```

---

**Next**: Learn about [Environment Configuration](./environment-configuration.md) for environment-aware service registration.
