<?php
declare(strict_types=1);

namespace App\Infrastructure\DI;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use App\Infrastructure\DI\Exception\ContainerException;
use App\Infrastructure\DI\Exception\NotFoundException;

/**
 * Simple PSR-11 compliant DI Container
 */
class Container implements PsrContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $singletons = [];

    public function get(string $id): mixed
    {
        // Return existing singleton instance
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        // Check if service is registered
        if (!$this->has($id)) {
            throw new NotFoundException("Service '{$id}' not found in container");
        }

        $binding = $this->bindings[$id];

        try {
            // Resolve the service
            if (is_callable($binding['concrete'])) {
                $instance = $binding['concrete']($this);
            } elseif (is_string($binding['concrete'])) {
                $instance = $this->build($binding['concrete']);
            } else {
                $instance = $binding['concrete'];
            }

            // Store singleton instances
            if ($binding['singleton']) {
                $this->instances[$id] = $instance;
            }

            return $instance;
        } catch (\Throwable $e) {
            throw new ContainerException("Error resolving '{$id}': " . $e->getMessage(), 0, $e);
        }
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    public function bind(string $abstract, mixed $concrete, bool $singleton = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, mixed $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Build a concrete class with automatic dependency injection
     */
    private function build(string $className): object
    {
        $reflection = new \ReflectionClass($className);

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
    private function resolveDependencies(\ReflectionMethod $constructor): array
    {
        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type || ($type instanceof \ReflectionNamedType && $type->isBuiltin())) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new ContainerException(
                        "Cannot resolve parameter '{$parameter->getName()}' in {$constructor->getDeclaringClass()->getName()}"
                    );
                }
            } else {
                $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : (string)$type;
                $dependencies[] = $this->get($typeName);
            }
        }

        return $dependencies;
    }
}
