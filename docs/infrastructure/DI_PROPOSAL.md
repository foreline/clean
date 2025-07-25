# Dependency Injection implementation considerations

The most needed place for DI is an EntityManager class, wich uses Entity Repository in its constructor. EntityManager is a Domain Layer, while Repositories belong to Infrastructure Layer. The Framework is designed to easily switch with persistence architecture - just implement the according Repository class and inject it in EntityManager class constructor.

## Recommended DI Patterns for EntityManager

### 1. Constructor Injection (Primary Pattern)
Direct dependency injection through constructor parameters - maintains Clean Architecture principles.


```php
namespace App\Domain\Post\UseCase;

use App\Domain\Post\Aggregate\Post;
use App\Domain\Post\Aggregate\PostCollection;
use App\Domain\Post\Repository\PostRepositoryInterface;

/**
 * Post Manager - Handles basic CRUD operations
 * 
 * This class demonstrates proper dependency injection for EntityManager classes.
 * The repository interface is injected via constructor, maintaining Clean Architecture
 * by depending on abstractions rather than concrete implementations.
 */
class PostManager
{
    private PostRepositoryInterface $repository;

    /**
     * @param PostRepositoryInterface $repository Repository implementation will be
     *                                           injected by DI container based on
     *                                           configuration bindings
     */
    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function persist(Post $post): Post
    {
        return $this->repository->persist($post);
    }

    public function findById(int $id): ?Post
    {
        return $this->repository->findById($id);
    }

    public function findAll(): PostCollection
    {
        return $this->repository->findAll();
    }

    public function delete(Post $post): void
    {
        $this->repository->delete($post);
    }
}
```

### 2. Service Provider Registration

```php
// src/Infrastructure/DI/Providers/DomainManagerServiceProvider.php
class DomainManagerServiceProvider implements ServiceProviderInterface
{
    public function register(ContainerInterface $container): void
    {
        // Bind repository interface to concrete implementation
        $container->bind(
            PostRepositoryInterface::class, 
            DatabasePostRepository::class
        );
        
        // Register manager with automatic dependency resolution
        $container->bind(PostManager::class, function (ContainerInterface $c) {
            return new PostManager(
                $c->get(PostRepositoryInterface::class)
            );
        });
    }
}
```

### 3. Container Configuration

```php
// config/dependencies.php
return [
    'bindings' => [
        // Repository bindings - easily switchable
        PostRepositoryInterface::class => DatabasePostRepository::class,
        UserRepositoryInterface::class => DatabaseUserRepository::class,
        
        // Alternative implementations for testing/different environments
        // PostRepositoryInterface::class => InMemoryPostRepository::class,
    ],
    
    'managers' => [
        PostManager::class => [
            'dependencies' => [PostRepositoryInterface::class],
            'singleton' => true,
        ],
    ],
];
```

### 4. Usage in Application Layer

```php
// In a Use Case or Application Service
class CreatePostUseCase
{
    private PostManager $postManager;
    
    public function __construct(PostManager $postManager)
    {
        $this->postManager = $postManager;
    }
    
    public function execute(CreatePostRequest $request): Post
    {
        $post = new Post(
            $request->getTitle(),
            $request->getContent()
        );
        
        return $this->postManager->persist($post);
    }
}
```

## Complete Implementation Example

The full DI implementation is demonstrated in `examples/Blog/` with the following structure:

```
examples/Blog/
├── demo.php                           # Complete working example
├── Infrastructure/
│   ├── Application.php                # Bootstrap with DI container
│   ├── DI/Container.php               # PSR-11 compliant container
│   └── Post/Repository/DatabasePostRepository.php
├── Presentation/HTTP/Controller/PostController.php  # Entry point
└── Post/UseCase/PostManager.php       # Updated with proper DI
```

### Entry Point Usage

```php
// demo.php - Application entry point
$app = new Application();              // Bootstrap DI container
$controller = new PostController($app); // Inject dependencies

// All dependencies are automatically resolved:
// PostController → PostManager → PostRepositoryInterface → DatabasePostRepository
$result = $controller->createPost(['title' => 'Hello DI', 'content' => 'Clean Architecture!']);
```

### Key Benefits Realized

1. **Zero Hard Dependencies**: Domain layer has no knowledge of infrastructure
2. **Easy Testing**: Mock any dependency by changing container bindings  
3. **Flexible Configuration**: Switch implementations via service providers
4. **Framework Ready**: PSR-11 compliant for Symfony/Laravel integration
5. **Performance**: Singleton pattern for expensive services

Run `php examples/Blog/demo.php` to see the complete implementation in action.

## Framework Integration Strategies

### ⚠️ **Avoiding Dual Kernel Problem**

When integrating with frameworks like Symfony, **avoid creating separate application kernels**:

```php
// ❌ WRONG: Dual kernels
$pristineApp = new PristineApplication();  // Pristine kernel
$symfonyKernel = new SymfonyKernel();      // Symfony kernel
```

### ✅ **Single Kernel Integration Patterns**

#### 1. Host Framework Primary (Recommended)
Use Symfony as primary framework, register Pristine services directly:

```php
# config/services.yaml
services:
    # Pristine services in Symfony container
    App\Domain\Post\Repository\PostRepositoryInterface:
        alias: App\Infrastructure\Post\Repository\DatabasePostRepository
        
    App\Domain\Post\UseCase\PostManager:
        arguments:
            $repository: '@App\Domain\Post\Repository\PostRepositoryInterface'
```

#### 2. Symfony Compiler Pass
```php
class PristineIntegrationPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container): void {
        // Register all Pristine services in Symfony container
        $container->register(PostManager::class)
            ->addArgument($container->getDefinition(PostRepositoryInterface::class));
    }
}
```

#### 3. Bundle Integration
```php
class PristineBundle extends Bundle {
    public function build(ContainerBuilder $container): void {
        $container->addCompilerPass(new PristineIntegrationPass());
    }
}
```

### Benefits of Single Kernel Approach:
- **No Configuration Conflicts**: Single configuration system
- **Better Performance**: One container, less memory overhead  
- **Unified Development**: Use native Symfony features (routing, events, cache)
- **Simpler Testing**: Standard Symfony test framework
- **Framework Features**: Access Symfony's full ecosystem

Run `php examples/Blog/symfony_integration_demo.php` to see single-kernel integration.

## Bitrix CMS Integration Strategies

### **Bitrix-Specific Challenges**

Bitrix CMS presents unique integration challenges unlike Symfony/Laravel:

1. **Legacy Global Functions**: `CUser`, `CIBlockElement`, `CModule::IncludeModule()`
2. **Non-PSR Architecture**: No PSR-11 container, custom ORM patterns
3. **Module Loading**: Requires specific initialization sequence
4. **Global State Dependency**: Heavy reliance on `$APPLICATION`, `$USER` globals
5. **Event System**: Uses `AddEventHandler` instead of modern dispatchers

### **Integration Patterns for Bitrix**

#### 1. **Wrapper/Adapter Pattern** (Recommended for Bitrix)

```php
// Infrastructure/Integration/Bitrix/Repository/BitrixPostRepository.php
class BitrixPostRepository implements PostRepositoryInterface
{
    private int $iblockId;
    
    public function __construct(int $iblockId = 1) 
    {
        // Ensure Bitrix modules are loaded
        \CModule::IncludeModule('iblock');
        $this->iblockId = $iblockId;
    }
    
    public function persist(Post $post): Post 
    {
        $fields = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $post->getTitle(),
            'DETAIL_TEXT' => $post->getContent(),
            'ACTIVE' => 'Y',
        ];
        
        $element = new \CIBlockElement;
        
        if ($post->getId()) {
            // Update existing
            $element->Update($post->getId(), $fields);
        } else {
            // Create new
            $id = $element->Add($fields);
            $post->setId($id);
        }
        
        return $post;
    }
    
    public function findById(int $id): ?Post 
    {
        $result = \CIBlockElement::GetByID($id);
        $data = $result->Fetch();
        
        if (!$data) return null;
        
        return $this->mapBitrixToPost($data);
    }
    
    private function mapBitrixToPost(array $data): Post 
    {
        $post = new Post();
        $post->setId((int)$data['ID']);
        $post->setTitle($data['NAME']);
        $post->setContent($data['DETAIL_TEXT']);
        
        return $post;
    }
}
```

#### 2. **Bitrix Module Integration**

```php
// Infrastructure/Integration/Bitrix/BitrixModuleManager.php
class BitrixModuleManager 
{
    private array $loadedModules = [];
    
    public function ensureModule(string $moduleId): void 
    {
        if (!in_array($moduleId, $this->loadedModules)) {
            if (!\CModule::IncludeModule($moduleId)) {
                throw new \RuntimeException("Cannot load Bitrix module: {$moduleId}");
            }
            $this->loadedModules[] = $moduleId;
        }
    }
    
    public function isModuleInstalled(string $moduleId): bool 
    {
        return \CModule::IncludeModule($moduleId);
    }
}
```

#### 3. **Bitrix Event Bridge**

```php
// Infrastructure/Integration/Bitrix/BitrixEventBridge.php
class BitrixEventBridge 
{
    public function bridgeDomainEvents(): void 
    {
        // Bridge Pristine domain events to Bitrix events
        \AddEventHandler('iblock', 'OnAfterIBlockElementAdd', 
            [$this, 'onElementAdd']);
        \AddEventHandler('iblock', 'OnAfterIBlockElementUpdate', 
            [$this, 'onElementUpdate']);
    }
    
    public function onElementAdd(&$arFields): void 
    {
        // Convert Bitrix event to Pristine domain event
        if ($arFields['IBLOCK_ID'] == 1) { // Posts iblock
            $event = new PostCreatedEvent($arFields['ID']);
            // Dispatch through Pristine event system
        }
    }
}
```

#### 4. **Bitrix-Aware Service Provider**

```php
// Infrastructure/Integration/Bitrix/BitrixServiceProvider.php
class BitrixServiceProvider implements ServiceProviderInterface 
{
    public function register(ContainerInterface $container): void 
    {
        // Ensure Bitrix environment
        if (!$this->isBitrixAvailable()) {
            throw new \RuntimeException('Bitrix CMS not available');
        }
        
        // Register Bitrix-specific implementations
        $container->bind(PostRepositoryInterface::class, function() {
            return new BitrixPostRepository(
                (int)\COption::GetOptionString('mymodule', 'posts_iblock_id', '1')
            );
        });
        
        // Register Bitrix utilities
        $container->bind(BitrixModuleManager::class, BitrixModuleManager::class, true);
        $container->bind(BitrixEventBridge::class, BitrixEventBridge::class, true);
    }
    
    private function isBitrixAvailable(): bool 
    {
        return defined('B_PROLOG_INCLUDED') || class_exists('CMain');
    }
}
```

#### 5. **Bitrix Component Integration**

```php
// Integration with Bitrix components
class BitrixPristineComponent extends \CBitrixComponent 
{
    private ContainerInterface $container;
    
    public function executeComponent(): void 
    {
        // Initialize Pristine container within Bitrix component
        $this->container = $this->initPristineContainer();
        
        // Use Pristine domain services
        $postManager = $this->container->get(PostManager::class);
        $posts = $postManager->findAll();
        
        $this->arResult['POSTS'] = $posts;
        $this->includeComponentTemplate();
    }
    
    private function initPristineContainer(): ContainerInterface 
    {
        $container = new Container();
        
        // Register Bitrix-specific providers
        $provider = new BitrixServiceProvider();
        $provider->register($container);
        
        return $container;
    }
}
```

### **Bitrix Integration Bootstrap**

```php
// bitrix_integration_bootstrap.php
// Include this in Bitrix init.php or component

// 1. Ensure Bitrix is loaded
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

// 2. Load Pristine autoloader  
require_once __DIR__ . '/vendor/autoload.php';

// 3. Initialize Pristine with Bitrix providers
$container = new \App\Infrastructure\DI\Container();

// Register Bitrix-specific services
$bitrixProvider = new \App\Infrastructure\Integration\Bitrix\BitrixServiceProvider();
$bitrixProvider->register($container);

// 4. Use Pristine domain services in Bitrix context
$postManager = $container->get(\App\Domain\Post\UseCase\PostManager::class);

// Now you can use clean domain logic within Bitrix!
```

### **Key Bitrix Integration Benefits:**

1. **Legacy Compatibility**: Work with existing Bitrix modules and data
2. **Gradual Migration**: Introduce clean architecture incrementally
3. **Bitrix Features**: Leverage Bitrix admin panel, permissions, caching
4. **Clean Domain**: Keep business logic separate from Bitrix specifics
5. **Testing**: Mock Bitrix dependencies for unit tests

### **Bitrix-Specific Considerations:**

- **Module Dependencies**: Always check and load required Bitrix modules
- **Global State**: Wrap Bitrix globals in service classes
- **Performance**: Use Bitrix caching mechanisms when possible
- **Security**: Leverage Bitrix permission system
- **Updates**: Ensure compatibility with Bitrix updates

### **Migration Strategy for Bitrix:**

1. **Phase 1**: Create Bitrix adapters for existing functionality
2. **Phase 2**: Gradually move business logic to Pristine domain layer  
3. **Phase 3**: Replace Bitrix-specific code with clean implementations
4. **Phase 4**: Use Bitrix only for presentation and data persistence