<?php
declare(strict_types=1);

namespace Infrastructure\DI\Bridge;

use Infrastructure\DI\ContainerInterface;
use Infrastructure\DI\ServiceProviderInterface;

/**
 * Bitrix CMS Bridge
 * 
 * Integrates Pristine DI services with Bitrix CMS environment,
 * handling module loading and global state management.
 */
class BitrixBridge implements ServiceProviderInterface
{
    private array $requiredModules = [];
    private bool $bitrixAvailable = false;

    public function __construct()
    {
        $this->bitrixAvailable = $this->checkBitrixAvailability();
    }

    /**
     * {@inheritdoc}
     */
    public function register(ContainerInterface $container): void
    {
        if (!$this->bitrixAvailable) {
            throw new \RuntimeException('Bitrix CMS is not available');
        }

        // Load required Bitrix modules
        $this->loadRequiredModules();

        // Register Bitrix-specific services
        $this->registerBitrixServices($container);

        // Register module manager
        $this->registerModuleManager($container);
    }

    /**
     * Require specific Bitrix module
     */
    public function requireModule(string $moduleId): void
    {
        $this->requiredModules[] = $moduleId;
    }

    /**
     * Register Bitrix-specific services
     */
    private function registerBitrixServices(ContainerInterface $container): void
    {
        // Register Bitrix user service
        $container->bind('bitrix.user', function() {
            global $USER;
            return $USER;
        }, true);

        // Register Bitrix application
        $container->bind('bitrix.application', function() {
            global $APPLICATION;
            return $APPLICATION;
        }, true);

        // Register Bitrix database connection
        $container->bind('bitrix.db', function() {
            global $DB;
            return $DB;
        }, true);

        // Register IBlock helper if iblock module is loaded
        if ($this->isModuleLoaded('iblock')) {
            $container->bind('bitrix.iblock', function() {
                return new BitrixIBlockHelper();
            }, true);
        }
    }

    /**
     * Register Bitrix module manager
     */
    private function registerModuleManager(ContainerInterface $container): void
    {
        $container->bind('bitrix.module_manager', function() {
            return new class {
                public function loadModule(string $moduleId): bool
                {
                    return \CModule::IncludeModule($moduleId);
                }

                public function isModuleInstalled(string $moduleId): bool
                {
                    return \CModule::IncludeModule($moduleId);
                }

                public function getModuleVersion(string $moduleId): ?string
                {
                    $moduleInfo = \CModule::CreateModuleObject($moduleId);
                    return $moduleInfo ? $moduleInfo->MODULE_VERSION : null;
                }
            };
        }, true);
    }

    /**
     * Load required Bitrix modules
     */
    private function loadRequiredModules(): void
    {
        foreach ($this->requiredModules as $moduleId) {
            if (!\CModule::IncludeModule($moduleId)) {
                throw new \RuntimeException("Cannot load required Bitrix module: {$moduleId}");
            }
        }
    }

    /**
     * Check if Bitrix CMS is available
     */
    private function checkBitrixAvailability(): bool
    {
        return defined('B_PROLOG_INCLUDED') || 
               class_exists('CMain') || 
               class_exists('CUser') ||
               function_exists('CModule::IncludeModule');
    }

    /**
     * Check if specific module is loaded
     */
    private function isModuleLoaded(string $moduleId): bool
    {
        return class_exists('CModule') && \CModule::IncludeModule($moduleId);
    }

    /**
     * Create bridge with common modules pre-loaded
     */
    public static function withCommonModules(): self
    {
        $bridge = new self();
        $bridge->requireModule('main');
        $bridge->requireModule('iblock');
        
        return $bridge;
    }

    /**
     * Create bridge for specific module set
     */
    public static function withModules(array $modules): self
    {
        $bridge = new self();
        foreach ($modules as $module) {
            $bridge->requireModule($module);
        }
        
        return $bridge;
    }
}

/**
 * Helper class for IBlock operations
 */
class BitrixIBlockHelper
{
    public function getElement(int $id): ?array
    {
        if (!class_exists('CIBlockElement')) {
            return null;
        }

        $result = \CIBlockElement::GetByID($id);
        return $result ? $result->Fetch() : null;
    }

    public function getElementsByFilter(array $filter): array
    {
        if (!class_exists('CIBlockElement')) {
            return [];
        }

        $elements = [];
        $result = \CIBlockElement::GetList([], $filter);
        
        while ($element = $result->Fetch()) {
            $elements[] = $element;
        }
        
        return $elements;
    }

    public function addElement(array $fields): int
    {
        if (!class_exists('CIBlockElement')) {
            throw new \RuntimeException('IBlock module not available');
        }

        $element = new \CIBlockElement();
        $id = $element->Add($fields);
        
        if (!$id) {
            throw new \RuntimeException('Failed to add IBlock element: ' . $element->LAST_ERROR);
        }
        
        return $id;
    }

    public function updateElement(int $id, array $fields): bool
    {
        if (!class_exists('CIBlockElement')) {
            throw new \RuntimeException('IBlock module not available');
        }

        $element = new \CIBlockElement();
        return $element->Update($id, $fields);
    }
}
