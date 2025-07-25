# Blog Application - Dependency Injection Implementation

This is a complete implementation of the Pristine Framework's Blog application demonstrating **Clean Architecture with Dependency Injection**.

## 🏗️ Architecture Overview

```
┌─────────────────────────┐
│    Presentation Layer   │  ← HTTP Controllers, CLI Commands
├─────────────────────────┤
│      Domain Layer       │  ← Business Logic, Use Cases, Entities
├─────────────────────────┤
│   Infrastructure Layer  │  ← Database, DI Container, External APIs
└─────────────────────────┘
```

## 📁 Project Structure

```
examples/Blog/
├── demo.php                           # Entry point demonstration
├── Infrastructure/
│   ├── Application.php                # Application bootstrap
│   ├── DI/
│   │   ├── Container.php              # PSR-11 DI Container
│   │   ├── ServiceProviderInterface.php
│   │   ├── Exception/                 # Container exceptions
│   │   └── Providers/
│   │       └── DomainServiceProvider.php
│   └── Post/Repository/
│       └── DatabasePostRepository.php # Repository implementation
├── Presentation/HTTP/Controller/
│   └── PostController.php             # HTTP controller
└── Post/                              # Domain layer (unchanged)
    ├── UseCase/PostManager.php        # Entity manager with DI
    ├── Repository/PostRepositoryInterface.php
    ├── Aggregate/Post.php
    └── Entity/PostEntity.php
```

## 🔄 Dependency Flow

1. **Application Bootstrap** (`Infrastructure/Application.php`)
   - Initializes DI container
   - Registers service providers
   - Configures bindings

2. **Service Provider** (`DomainServiceProvider.php`)
   ```php
   $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
   $container->bind(PostManager::class, function($c) {
       return new PostManager($c->get(PostRepositoryInterface::class));
   });
   ```

3. **Controller** (`PostController.php`)
   ```php
   public function __construct(Application $app) {
       $this->postManager = $app->get(PostManager::class);
   }
   ```

4. **Domain Manager** (`PostManager.php`)
   ```php
   public function __construct(PostRepositoryInterface $repository) {
       $this->repository = $repository;
   }
   ```

## 🚀 Running the Demo

```bash
cd examples/Blog
php demo.php
```

## 💡 Key DI Patterns Demonstrated

### 1. **Constructor Injection**
- `PostManager` receives `PostRepositoryInterface` via constructor
- No hard dependencies on concrete implementations
- Easy to test with mock objects

### 2. **Interface Segregation**
- Domain depends on `PostRepositoryInterface` (abstraction)
- Infrastructure provides `DatabasePostRepository` (concrete)
- Easy to swap implementations (Database → Memory → API)

### 3. **Service Provider Pattern**
- Organized registration of services
- Separation of concerns for different modules
- Clean configuration management

### 4. **Singleton Pattern**
- Managers are singleton by default
- Repositories can be singleton for performance
- Configurable per service

### 5. **Container Bridge Pattern**
- Ready for integration with Symfony/Laravel
- PSR-11 compliance
- Framework-agnostic design

## 🔧 Configuration

Services are configured in `DomainServiceProvider.php`:

```php
// Bind interface to implementation
$container->bind(
    PostRepositoryInterface::class,
    DatabasePostRepository::class,
    true // singleton
);

// Register manager with dependencies
$container->bind(PostManager::class, function (Container $c) {
    return new PostManager(
        $c->get(PostRepositoryInterface::class)
    );
}, true);
```

## 🧪 Testing Benefits

With proper DI, testing becomes trivial:

```php
class PostManagerTest extends TestCase {
    public function testPersist() {
        $mockRepo = $this->createMock(PostRepositoryInterface::class);
        $manager = new PostManager($mockRepo);
        
        // Test business logic without database
    }
}
```

## 🔄 Framework Integration

To integrate with Symfony:

```php
// Symfony service definition
services:
    App\Domain\Post\UseCase\PostManager:
        arguments:
            - '@App\Domain\Post\Repository\PostRepositoryInterface'
            
    App\Domain\Post\Repository\PostRepositoryInterface:
        class: App\Infrastructure\Post\Repository\DatabasePostRepository
```

## ✅ Clean Architecture Benefits

1. **Testability**: Easy to mock dependencies
2. **Flexibility**: Swap implementations without changing domain code  
3. **Maintainability**: Clear separation of concerns
4. **Scalability**: Add new features without touching existing code
5. **Framework Independence**: Domain logic is framework-agnostic

## 🎯 Next Steps

1. Add more sophisticated container features (auto-discovery, compilation)
2. Implement event dispatching with DI
3. Add validation services
4. Create framework bridge adapters
5. Add configuration-based binding
