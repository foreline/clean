<?php
declare(strict_types=1);

namespace Infrastructure\DI\Configuration;

use InvalidArgumentException;

/**
 * Environment Configuration
 * 
 * Manages environment-based configuration for dependency injection,
 * allowing different service bindings for development, testing, and production.
 */
class EnvironmentConfiguration
{
    private string $environment;
    private array $bindings = [];

    public function __construct(string $environment = null)
    {
        $this->environment = $environment ?? $this->detectEnvironment();
    }

    /**
     * Set environment-specific bindings
     * 
     * @param string $environment Environment name (dev, test, prod)
     * @param array $bindings Array of service bindings
     */
    public function setBindings(string $environment, array $bindings): void
    {
        $this->bindings[$environment] = $bindings;
    }

    /**
     * Get bindings for current environment
     */
    public function getBindings(): array
    {
        return $this->bindings[$this->environment] ?? [];
    }

    /**
     * Get bindings for specific environment
     */
    public function getBindingsFor(string $environment): array
    {
        return $this->bindings[$environment] ?? [];
    }

    /**
     * Get current environment
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Set environment
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * Check if in development environment
     */
    public function isDevelopment(): bool
    {
        return in_array($this->environment, ['development', 'dev']);
    }

    /**
     * Check if in testing environment
     */
    public function isTesting(): bool
    {
        return in_array($this->environment, ['testing', 'test']);
    }

    /**
     * Check if in production environment
     */
    public function isProduction(): bool
    {
        return in_array($this->environment, ['production', 'prod']);
    }

    /**
     * Load configuration from array
     * 
     * Expected format:
     * [
     *     'development' => [
     *         'Interface' => 'DevelopmentImplementation',
     *     ],
     *     'production' => [
     *         'Interface' => 'ProductionImplementation',
     *     ]
     * ]
     */
    public function loadFromArray(array $config): void
    {
        $this->bindings = $config;
    }

    /**
     * Load configuration from file
     */
    public function loadFromFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Configuration file not found: {$filePath}");
        }

        $config = require $filePath;
        
        if (!is_array($config)) {
            throw new InvalidArgumentException("Configuration file must return an array");
        }

        $this->loadFromArray($config);
    }

    /**
     * Auto-detect environment from various sources
     */
    private function detectEnvironment(): string
    {
        // Check environment variable
        $env = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV');
        if ($env) {
            return $env;
        }

        // Check for common development indicators
        if (defined('ENVIRONMENT')) {
            return constant('ENVIRONMENT');
        }

        // Default to production for safety
        return 'production';
    }
}
