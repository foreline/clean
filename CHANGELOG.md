# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- EntityPermissionsInterface and AbstractEntityPermissions for CRUD authorization checks in the business logic layer.

### Deprecated
- AbstractEntity URL methods (getDetailPageUrl, setDetailPageUrl, getSlug, setSlug, getListUrl, setListUrl, getAddSlug, setAddSlug) are now marked as deprecated and will be removed in v2.0. Use domain-specific URL generation instead.

### Removed
- BitrixBridge integration (no longer maintained).
- File-based DTO classes (Field, FieldType, FormType) superseded by modern alternatives.
- Legacy Presentation helpers (Ajax, Asset) in favor of modern solutions.
- EventStoreRepositoryInterface from incomplete event store implementation.

### Changed
- Improved entity permissions type hints and consolidation: replaced mixed types with specific type hints (AggregateInterface, ServiceInterface) for better static analysis. Consolidated EntityPermissionsInterface into AbstractEntityPermissions.
- Improved event-driven architecture: Subscriber and SubscriberInterface now accept EventInterface for polymorphic event handling.
- Refactored Scheduler architecture with improved task ordering and execution logic in SchedulerHelper and TaskHandler.
- Enhanced User aggregate and File use cases with cleaner signatures and better separation of concerns.
- Improved DI Container resolution logic and Environment Configuration handling.
- Simplified AbstractManager use case base class and GetCollectionInterface contracts.

### Fixed
- MailerManager now safely handles null mailer with null-safe operator to prevent null reference errors.

## [1.1.3] - 2025-01-XX

### Added
- Min and max count for grouped results.

## [1.1.2] - 2025-01-XX

### Added
- Aggregation group functionality.

## [1.1.1] - 2025-01-XX

### Fixed
- Fixed method signature issues.

## [1.0.03] - 2025-01-XX

### Added
- Extended exception data information.

## [1.0.02] - 2025-01-XX

### Changed
- Improved entity permissions type hints and consolidation: replaced mixed types with specific type hints (AggregateInterface, ServiceInterface) for better static analysis. Consolidated EntityPermissionsInterface into AbstractEntityPermissions.
- Internal restructuring.
