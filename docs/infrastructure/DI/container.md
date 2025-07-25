# Container Usage Guide

This guide covers the fundamental usage patterns of the Pristine Framework DI Container.

## 📋 Table of Contents

1. [Basic Usage](#basic-usage)
2. [Service Binding](#service-binding)
3. [Service Resolution](#service-resolution)
4. [Singleton Services](#singleton-services)
5. [Autowiring](#autowiring)
6. [Error Handling](#error-handling)
7. [Best Practices](#best-practices)

## 🚀 Basic Usage

### Creating a Container

```php
use Infrastructure\DI\Container;

$container = new Container();
```

### Basic Service Binding

```php
// Bind interface to implementation
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);

// Bind with closure factory
$container->bind(Logger::class, function(ContainerInterface $container) {
    return new FileLogger('/path/to/logs');
});

// Bind concrete instance
$config = new Configuration(['debug' => true]);
$container->bind(Configuration::class, $config);
```

## 🔧 Service Binding

### Interface to Implementation Binding

```php
// Domain service binding
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
$container->bind(UserRepositoryInterface::class, DatabaseUserRepository::class);
$container->bind(EmailServiceInterface::class, SmtpEmailService::class);
```

### Factory Functions

```php
// Complex service creation
$container->bind(DatabaseConnection::class, function(ContainerInterface $container) {
    $config = $container->get(Configuration::class);
    
    return new PDO(
        $config->get('database.dsn'),
        $config->get('database.username'),
        $config->get('database.password'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
});

// Service with dependencies
$container->bind(PostManager::class, function(ContainerInterface $container) {
    return new PostManager(
        $container->get(PostRepositoryInterface::class),
        $container->get(EmailServiceInterface::class),
        $container->get(Logger::class)
    );
});
```

### Conditional Binding

```php
// Environment-based binding
if ($_ENV['APP_ENV'] === 'development') {
    $container->bind(PostRepositoryInterface::class, InMemoryPostRepository::class);
} else {
    $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
}

// Feature flag binding
if ($container->get(Configuration::class)->get('features.advanced_logging')) {
    $container->bind(Logger::class, AdvancedLogger::class);
} else {
    $container->bind(Logger::class, SimpleLogger::class);
}
```

## 🎯 Service Resolution

### Basic Resolution

```php
// Get service instance
$postRepository = $container->get(PostRepositoryInterface::class);

// Check if service exists
if ($container->has(PostRepositoryInterface::class)) {
    $postRepository = $container->get(PostRepositoryInterface::class);
}
```

### Automatic Dependency Injection

```php
class PostController
{
    public function __construct(
        PostRepositoryInterface $postRepository,
        EmailServiceInterface $emailService,
        Logger $logger
    ) {
        $this->postRepository = $postRepository;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }
}

// Container automatically resolves all dependencies
$controller = $container->get(PostController::class);
```

### Method Resolution

```php
// Resolve method dependencies
class PostService
{
    public function createPost(
        CreatePostRequest $request,
        PostRepositoryInterface $repository,
        EmailServiceInterface $emailService
    ): Post {
        // Implementation
    }
}

// Resolve method call with dependencies
$postService = $container->get(PostService::class);
$result = $container->call([$postService, 'createPost'], ['request' => $request]);
```

## 🔄 Singleton Services

### Registering Singletons

```php
// Register as singleton - same instance returned every time
$container->singleton(Logger::class, FileLogger::class);
$container->singleton(Configuration::class, Configuration::class);

// Singleton with factory
$container->singleton(DatabaseConnection::class, function(ContainerInterface $container) {
    return new DatabaseConnection($container->get(Configuration::class));
});
```

### Singleton vs Transient

```php
// Transient - new instance every time
$container->bind(PostProcessor::class, PostProcessor::class);
$processor1 = $container->get(PostProcessor::class);
$processor2 = $container->get(PostProcessor::class);
// $processor1 !== $processor2

// Singleton - same instance every time
$container->singleton(Configuration::class, Configuration::class);
$config1 = $container->get(Configuration::class);
$config2 = $container->get(Configuration::class);
// $config1 === $config2
```

### Checking Singleton Status

```php
if ($container->isSingleton(Logger::class)) {
    echo "Logger is registered as singleton";
}
```

## ⚡ Autowiring

### Automatic Constructor Resolution

```php
class PostManager
{
    public function __construct(
        PostRepositoryInterface $postRepository,
        UserRepositoryInterface $userRepository,
        EmailServiceInterface $emailService
    ) {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }
}

// No explicit binding needed if dependencies are bound
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
$container->bind(UserRepositoryInterface::class, DatabaseUserRepository::class);
$container->bind(EmailServiceInterface::class, SmtpEmailService::class);

// Autowiring resolves all dependencies automatically
$postManager = $container->get(PostManager::class);
```

### Scalar Parameter Handling

```php
class DatabaseRepository
{
    public function __construct(
        PDO $connection,
        string $tableName,  // Cannot be autowired
        bool $debugMode = false
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->debugMode = $debugMode;
    }
}

// Must use factory for scalar parameters
$container->bind(DatabaseRepository::class, function(ContainerInterface $container) {
    return new DatabaseRepository(
        $container->get(PDO::class),
        'posts',
        $container->get(Configuration::class)->get('debug')
    );
});
```

### Interface Type Hints

```php
// Autowiring works with interface type hints
class OrderProcessor
{
    public function __construct(
        PaymentServiceInterface $paymentService,
        InventoryServiceInterface $inventoryService,
        NotificationServiceInterface $notificationService
    ) {
        // Dependencies automatically resolved if bound
    }
}
```

## ❌ Error Handling

### Common Exceptions

```php
use Infrastructure\DI\Exception\ServiceNotFoundException;
use Infrastructure\DI\Exception\CircularDependencyException;
use Infrastructure\DI\Exception\AutowireException;

try {
    $service = $container->get('NonExistentService');
} catch (ServiceNotFoundException $e) {
    echo "Service not found: " . $e->getMessage();
}

try {
    // Circular dependency A -> B -> A
    $container->bind(ServiceA::class, ServiceA::class);
    $container->bind(ServiceB::class, ServiceB::class);
    $service = $container->get(ServiceA::class);
} catch (CircularDependencyException $e) {
    echo "Circular dependency detected: " . $e->getMessage();
}

try {
    // Cannot autowire scalar parameters
    $service = $container->get(ServiceWithScalarParams::class);
} catch (AutowireException $e) {
    echo "Autowiring failed: " . $e->getMessage();
}
```

### Safe Resolution

```php
// Check before resolving
if ($container->has(OptionalService::class)) {
    $service = $container->get(OptionalService::class);
} else {
    $service = new DefaultService();
}

// Try-catch pattern
try {
    $service = $container->get(PreferredService::class);
} catch (ServiceNotFoundException $e) {
    $service = $container->get(FallbackService::class);
}
```

## 📚 Best Practices

### 1. Use Interfaces for Binding

```php
// ✅ Good - Bind interfaces to implementations
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
$container->bind(EmailServiceInterface::class, SmtpEmailService::class);

// ❌ Avoid - Binding concrete classes
$container->bind(DatabasePostRepository::class, DatabasePostRepository::class);
```

### 2. Prefer Constructor Injection

```php
// ✅ Good - Constructor injection
class PostController
{
    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}

// ❌ Avoid - Service locator pattern
class PostController
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function index()
    {
        $repository = $this->container->get(PostRepositoryInterface::class);
    }
}
```

### 3. Register Services Early

```php
// ✅ Good - Register all services at application boot
function bootstrapContainer(): ContainerInterface
{
    $container = new Container();
    
    // Register all services
    $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
    $container->bind(UserRepositoryInterface::class, DatabaseUserRepository::class);
    $container->singleton(Configuration::class, Configuration::class);
    
    return $container;
}
```

### 4. Use Factories for Complex Services

```php
// ✅ Good - Factory for complex setup
$container->bind(EmailService::class, function(ContainerInterface $container) {
    $config = $container->get(Configuration::class);
    
    $service = new EmailService();
    $service->setHost($config->get('email.host'));
    $service->setCredentials(
        $config->get('email.username'),
        $config->get('email.password')
    );
    
    return $service;
});
```

### 5. Test Container Configuration

```php
// Test your bindings
class ContainerTest extends TestCase
{
    public function testServiceBindings()
    {
        $container = $this->createContainer();
        
        $this->assertTrue($container->has(PostRepositoryInterface::class));
        $this->assertInstanceOf(
            PostRepositoryInterface::class,
            $container->get(PostRepositoryInterface::class)
        );
    }
    
    public function testSingletons()
    {
        $container = $this->createContainer();
        
        $config1 = $container->get(Configuration::class);
        $config2 = $container->get(Configuration::class);
        
        $this->assertSame($config1, $config2);
    }
}
```

## 🔄 Container Lifecycle

### Container Cleanup

```php
// Clear all services (useful for testing)
$container->clear();

// Check what's registered
$hasLogger = $container->has(Logger::class);
$isSingleton = $container->isSingleton(Configuration::class);
```

### Container Composition

```php
// Delegate to parent container for missing services
class ChildContainer extends Container
{
    private ContainerInterface $parent;
    
    public function get(string $id)
    {
        try {
            return parent::get($id);
        } catch (ServiceNotFoundException $e) {
            return $this->parent->get($id);
        }
    }
}
```

---

**Next**: Learn about [Service Providers](./service-providers.md) for better service organization.
