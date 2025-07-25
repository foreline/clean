# Dependency Injection (DI) - Experimental

> **⚠️ EXPERIMENTAL FEATURE**: The Dependency Injection system is currently experimental and may change in future versions. Use with caution in production environments.

The Pristine Framework now includes an optional, lightweight Dependency Injection container that maintains Clean Architecture principles while providing modern DI capabilities.

## 🚀 Quick Start

```php
use Infrastructure\DI\Container;

// Create container
$container = new Container();

// Bind services
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);

// Resolve with automatic dependency injection
$postManager = $container->get(PostManager::class);
```

## 📚 Documentation Sections

### Core Concepts
- **[Container Usage](./container.md)** - Basic container operations and patterns
- **[Service Providers](./service-providers.md)** - Organizing service registration
- **[Environment Configuration](./environment-configuration.md)** - Environment-based bindings

### Framework Integration
- **[Framework Integration](./framework-integration.md)** - Integration strategies and patterns
- **[Symfony Integration](./symfony-integration.md)** - Complete Symfony integration guide
- **[Bitrix Integration](./bitrix-integration.md)** - Bitrix CMS integration guide

### Migration and Examples
- **[Migration Guide](./migration-guide.md)** - Adding DI to existing projects
- **[Examples](./examples/)** - Practical usage examples

## ✨ Key Features

### **Optional by Design**
- **Zero Breaking Changes**: Existing code continues to work unchanged
- **Opt-in Usage**: Enable DI only where needed
- **Backward Compatible**: Framework functions with or without DI

### **PSR-11 Compliant**
- **Standard Interface**: Compatible with other PSR-11 containers
- **Framework Agnostic**: Easy integration with Symfony, Laravel, etc.
- **Professional Grade**: Production-ready container implementation

### **Clean Architecture Focused**
- **Domain Independence**: Domain layer remains framework-agnostic
- **Dependency Inversion**: Depend on abstractions, not implementations
- **Easy Testing**: Mock dependencies effortlessly

### **Environment Aware**
- **Development/Production**: Different bindings per environment
- **Auto-Detection**: Automatic environment detection
- **Configuration Flexibility**: Programmatic or file-based configuration

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────┐
│                  Application Layer                  │
├─────────────────────────────────────────────────────┤
│              Presentation Layer                     │  ← Controllers, CLI
│  ┌─────────────────────────────────────────────┐   │
│  │           DI Container                      │   │
│  │  ┌─────────────────────────────────────┐   │   │
│  │  │         Service Providers           │   │   │
│  │  │  • Domain Services                  │   │   │
│  │  │  • Infrastructure Services          │   │   │
│  │  │  • Framework Bridges                │   │   │
│  │  └─────────────────────────────────────┘   │   │
│  └─────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────┤
│                Domain Layer                         │  ← Business Logic
│           (DI Optional - Pure POPO)                 │
├─────────────────────────────────────────────────────┤
│              Infrastructure Layer                   │  ← Data Access, External APIs
│  • Repository Implementations                      │
│  • Framework Bridges                               │
│  • External Service Adapters                       │
└─────────────────────────────────────────────────────┘
```

## 🎯 Usage Patterns

### **1. Constructor Injection**
```php
class PostManager
{
    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}
```

### **2. Service Provider Registration**
```php
class DomainServiceProvider extends AbstractServiceProvider
{
    protected function registerServices(ContainerInterface $container): void
    {
        $container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
        $container->singleton(PostManager::class, PostManager::class);
    }
}
```

### **3. Environment-Based Configuration**
```php
// Development
$container->bind(PostRepositoryInterface::class, InMemoryPostRepository::class);

// Production  
$container->bind(PostRepositoryInterface::class, DatabasePostRepository::class);
```

## 🔧 Framework Integration

The DI system includes built-in bridges for popular frameworks:

### **Symfony Integration**
```php
// Register Pristine services in Symfony container
$bridge = new SymfonyBridge($symfonyContainer);
$pristineContainer->registerProvider($bridge);
```

### **Bitrix Integration**
```php
// Integrate with Bitrix CMS
$bridge = BitrixBridge::withCommonModules();
$container->registerProvider($bridge);
```

## 📋 Requirements

- **PHP 8.0+**: Uses modern PHP features
- **PSR-11**: Compatible with PSR-11 container interface
- **Optional Extensions**: Framework-specific features may require additional packages

## 🚀 Getting Started

1. **[Read the Container Guide](./container.md)** - Learn basic container usage
2. **[Set Up Service Providers](./service-providers.md)** - Organize your services
3. **[Configure Environment](./environment-configuration.md)** - Set up environment-based bindings
4. **[Follow Migration Guide](./migration-guide.md)** - Add DI to existing projects

## ⚠️ Important Notes

### **Experimental Status**
- API may change in future versions
- Thorough testing recommended before production use
- Feedback and contributions welcome

### **Optional Nature**
- Framework works perfectly without DI
- Introduce gradually in existing projects
- No pressure to refactor existing code

### **Performance Considerations**
- Minimal overhead when not used
- Efficient autowiring and caching
- Production-ready performance characteristics

---

**Next Steps**: Start with the [Container Usage Guide](./container.md) to learn the basics.
