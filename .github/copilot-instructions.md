# Project: Pristine Framework (foreline/clean)

PHP Clean Architecture framework providing base abstractions for building domain-driven applications.

## Tech Stack
- PHP 8.1+ with `declare(strict_types=1)`
- Symfony components: HttpFoundation, Scheduler, Messenger
- ramsey/uuid for UUID generation
- webmozart/assert for input validation
- PHPUnit 9.6 for testing, PHPStan for static analysis

## Architecture
- **Clean Architecture** with three layers: `Domain`, `Infrastructure`, `Presentation`
- Namespaces map directly to layers: `Domain\`, `Infrastructure\`, `Presentation\`
- Dependencies point inward: Presentation → Infrastructure → Domain
- Domain layer has zero dependencies on Infrastructure or Presentation
- **Anemic model**: entities are simple data holders, business logic lives in services/use cases (Managers)
- **Event-driven**: domain events for cross-cutting concerns via `Publisher`/`Subscriber`

## Key Domain Concepts
- **Entity** (`AbstractEntity`): base class with id, name, slug; fluent setters returning `self`
- **Aggregate** (`AbstractAggregate`): extends Entity, implements `AggregateInterface`
- **Value Object**: immutable, typed interfaces (`StringValueObjectInterface`, `IntValueObjectInterface`, `Email`, `IPv4`, `Money`, `Color`)
- **Repository** (`RepositoryInterface`): data access with `Filter`, `Sort`, `Limit`, `Fields`, `Group`
- **Use Case / Manager** (`AbstractManager`): orchestrates domain operations
- **Domain Event** (`Event`): carries `occurredOn` timestamp and optional user context

## Coding Style
- Always include `declare(strict_types=1)` after the opening `<?php` tag
- 4-space indentation, no tabs
- PSR-4 autoloading; namespace matches directory structure
- PHPDoc blocks on all public/protected methods with `@param`, `@return`, `@throws`
- Fluent setters: `public function setX(Type $x): self`
- Spaces inside conditionals: `if ( null === $id )` (Yoda conditions)
- Interfaces suffixed with `Interface`, abstract classes prefixed with `Abstract`, traits suffixed with `Trait`
- One class per file; filename matches class name

## Error Handling
- Use specific exception classes under `Domain\Exception\` and `Infrastructure\DI\Exception\`
- Validate at boundaries using `webmozart/assert` or `InvalidArgumentException`
- Never swallow exceptions silently unless explicitly justified

## Testing
- PHPUnit 9.6, test files in `tests/` mirroring `src/` structure
- Class naming: `{ClassName}Test` extending `PHPUnit\Framework\TestCase`
- Use AAA pattern: Arrange, Act, Assert with comments
- Use `setUp()` for shared fixtures
- Run tests: `vendor/bin/phpunit`
- Run static analysis: `composer phpstan`

## Commands
- `vendor/bin/phpunit` — run all tests
- `composer phpstan` — run PHPStan analysis
- `bin/messenger-worker.php` — start Symfony Messenger worker
- `bin/event-monitor.php` — start event monitoring
