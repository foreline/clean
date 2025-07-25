<?php
declare(strict_types=1);

namespace Infrastructure\DI;

use Infrastructure\DI\Exception\ContainerException;
use Infrastructure\DI\Exception\NotFoundException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Pristine Framework DI Container
 * 
 * PSR-11 compliant dependency injection container with autowiring,
 * singleton support, and service provider integration.
 * 
 * @package Infrastructure\DI
 */
class Container implements ContainerInterface
{
    /** @var array Service bindings */
    private array $bindings = [];
    
    /** @var array Singleton instances */
    private array $instances = [];
    
    /** @var array Circular dependency detection */
    private array $resolving = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $id): mixed
    {
        // Return existing singleton instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Check for circular dependencies
        if (isset($this->resolving[$id])) {
            throw new ContainerException("Circular dependency detected for service '{$id}'");
        }

        // Mark as resolving
        $this->resolving[$id] = true;

        try {
            // Check if service is registered
            if (!$this->has($id)) {
                // Try to auto-resolve if it's a class
                if (class_exists($id)) {
                    $instance = $this->build($id);
                } else {
                    throw new NotFoundException("Service '{$id}' not found in container");
                }
            } else {
                $binding = $this->bindings[$id];
                $instance = $this->resolve($binding);

                // Store singleton instances
                if ($binding['singleton']) {
                    $this->instances[$id] = $instance;
                }
            }

            // Remove from resolving
            unset($this->resolving[$id]);

            return $instance;
        } catch (\Throwable $e) {
            // Clean up resolving state
            unset($this->resolving[$id]);
            
            if ($e instanceof ContainerException || $e instanceof NotFoundException) {
                throw $e;
            }
            
            throw new ContainerException("Error resolving '{$id}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(string $abstract, mixed $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];

        // Remove existing singleton instance if rebinding
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * {@inheritdoc}
     */
    public function registerProvider(ServiceProviderInterface $provider): void
    {
        $provider->register($this);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleton(string $id): bool
    {
        return isset($this->bindings[$id]) && $this->bindings[$id]['singleton'];
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->bindings = [];
        $this->instances = [];
        $this->resolving = [];
    }

    /**
     * Resolve a service binding
     */
    private function resolve(array $binding): mixed
    {
        $concrete = $binding['concrete'];

        if (is_callable($concrete)) {
            return $concrete($this);
        }

        if (is_string($concrete)) {
            return $this->build($concrete);
        }

        return $concrete;
    }

    /**
     * Build a concrete class with automatic dependency injection
     */
    private function build(string $className): object
    {
        try {
            $reflection = new ReflectionClass($className);
        } catch (\ReflectionException $e) {
            throw new ContainerException("Class '{$className}' does not exist");
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("Class '{$className}' is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $className();
        }

        $dependencies = $this->resolveDependencies($constructor);

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(ReflectionMethod $constructor): array
    {
        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type || ($type instanceof ReflectionNamedType && $type->isBuiltin())) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new ContainerException(
                        "Cannot resolve parameter '{$parameter->getName()}' in {$constructor->getDeclaringClass()->getName()}"
                    );
                }
            } else {
                $typeName = $type instanceof ReflectionNamedType ? $type->getName() : (string)$type;
                
                try {
                    $dependencies[] = $this->get($typeName);
                } catch (NotFoundException $e) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        throw new ContainerException(
                            "Cannot resolve dependency '{$typeName}' for parameter '{$parameter->getName()}' in {$constructor->getDeclaringClass()->getName()}"
                        );
                    }
                }
            }
        }

        return $dependencies;
    }
}
