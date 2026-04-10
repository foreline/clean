# Pristine Framework Roadmap

## Versioning Strategy

Every breaking change follows a two-release cycle:

1. **Prep release** (minor version): ships deprecation warnings, migration validation tools (PHPStan rules, CLI scripts), and migration documentation. Consumer code continues to work unchanged.
2. **Breaking release** (next major version): removes deprecated code. Consumers who ran the validation tools and followed the migration guide experience zero surprises.

Each breaking phase gets its own major version to protect consumers from large batches of breaking changes:

| Phase | Prep Release | Breaking Release |
|-------|-------------|------------------|
| 1 — Return Types | `1.x` | `2.0.0` |
| 2 — AbstractEntity Cleanup | `2.x` | `3.0.0` |
| 3 — Deprecated API Removal | `3.x` | `4.0.0` |
| 4 — Event System Interfaces | `4.x` | `5.0.0` |
| 5 — Bitrix Decoupling | `5.x` | `6.0.0` |
| 6 — Encapsulation & Quality | `6.x` | `7.0.0` |

**Rule**: no code is removed until migration tools for that removal have been available for at least one release.

---

## Phase 1 — Type Safety & Return Type Consistency

**Goal**: Fix return type inconsistencies so PHPStan can be raised above level 0.

This is the foundation. Nothing else can be validated until the type system is honest.

### 1a — Prep release

- **Audit**: catalog every fluent setter across the codebase and its return type (`self`, `static`, `$this`, concrete class)
- **Decision**: standardize on `static` for all fluent setters on abstract/base classes, `self` on final/leaf classes
- **Ship PHPStan custom rule** (`tools/phpstan/FluentReturnTypeRule.php`): reports any setter returning the wrong type
- **Ship CLI migration script** (`tools/migrate-return-types.php`): scans consumer code extending framework classes and reports setters with incorrect return types
- **Raise PHPStan to level 3** in `phpstan.neon` and fix all violations within the framework
- **Document**: migration guide explaining the return type convention

### 1b — Breaking release

- All fluent setters use `static` consistently
- PHPStan level 3 is the enforced minimum
- CI fails on return type violations

---

## Phase 2 — AbstractEntity Cleanup

**Goal**: Remove presentation concerns (`detailPageUrl`, `listUrl`, `addUrl`) from `AbstractEntity`.

### 2a — Prep release

- **Deprecate** `getDetailPageUrl()`, `setDetailPageUrl()`, `getListUrl()`, `setListUrl()`, `getAddUrl()`, `setAddUrl()` with `@deprecated` tags and `trigger_deprecation()` calls at runtime
- **Ship PHPStan custom rule** (`tools/phpstan/DeprecatedEntityMethodsRule.php`): reports any usage of deprecated `AbstractEntity` methods in consumer code
- **Ship CLI migration script** (`tools/migrate-entity-urls.php`): scans consumer code, reports usages, suggests replacements (move URL logic to Presentation layer DTOs or view models)
- **Introduce** `SlugInterface` / `SlugTrait` as an opt-in trait for entities that need a slug
- **`aggregatedCount`**: stays on `AbstractEntity` for now to avoid breaking changes. A separate issue will track evaluating a move to `CollectionInterface::count()` or a dedicated `AggregatedResult` wrapper in a future phase

### 2b — Breaking release

- Remove `detailPageUrl`, `listUrl`, `addUrl` properties and all associated getters/setters from `AbstractEntity`
- `SlugInterface` is the official way to add slug support

---

## Phase 3 — Deprecated API Removal

**Goal**: Remove the legacy API surface (`IteratorInterface`, `IteratorTrait`, `PersistedValueObjectInterface`, deprecated response classes).

### 3a — Prep release

- Add `trigger_deprecation()` to all deprecated classes/methods that don't already have it
- **Ship PHPStan custom rule** (`tools/phpstan/DeprecatedApiRule.php`): comprehensive rule detecting usage of any deprecated class, interface, or method
- **Ship CLI migration script** (`tools/migrate-deprecated-api.php`):
  - `IteratorInterface` → `CollectionInterface` (rename, adjust method signatures)
  - `IteratorTrait` → `CollectionTrait`
  - `PersistedValueObjectInterface` → `PersistableValueObjectInterface`
  - Deprecated Presentation response classes → their replacements
- **Document**: complete mapping table of old → new names in migration guide

### 3b — Breaking release

- Remove `IteratorInterface`, `IteratorTrait`, `PersistedValueObjectInterface`
- Remove deprecated Presentation response classes
- Remove any `@deprecated` code that has had migration tooling for at least one release

---

## Phase 4 — Event System: Interfaces Over Concrete Classes

**Goal**: `SubscriberInterface::handle()` and `isSubscribedTo()` accept `EventInterface`, not `Event`. Break the `Event` → `GetCurrentUser` → `UserManager` hidden dependency chain.

### 4a — Prep release

- **Change `SubscriberInterface`**:
  - `handle(EventInterface $event): void` (currently `Event`)
  - `isSubscribedTo(EventInterface $event): bool` (currently `Event|EventInterface`)
- **Introduce `UserProviderInterface`** in `Domain\User\Service\`:
  ```php
  interface UserProviderInterface
  {
      public function getCurrentUser(): ?UserInterface;
  }
  ```
- **Add static user provider to `Event`** (backward-compatible bridge):
  ```php
  class Event implements EventInterface
  {
      private static ?UserProviderInterface $userProvider = null;

      public static function setUserProvider(UserProviderInterface $provider): void
      {
          self::$userProvider = $provider;
      }

      public function __construct()
      {
          $this->occurredOn = new DateTimeImmutable();
          if (null !== self::$userProvider) {
              $this->user = self::$userProvider->getCurrentUser();
          }
      }
  }
  ```
- **Deprecate** the old `GetCurrentUser` direct instantiation pattern in `Event`
- **Ship PHPStan rule**: detect any `SubscriberInterface` implementation that type-hints `Event` instead of `EventInterface`
- **Document**: migration guide for subscriber implementations

### 4b — Breaking release

- `Event` constructor no longer calls `new GetCurrentUser()` — requires explicit `Event::setUserProvider()` during bootstrap
- All subscriber signatures use `EventInterface`
- Remove `Event|EventInterface` union type — only `EventInterface`

---

## Phase 5 — Bitrix Decoupling

**Goal**: The `foreline/clean` Composer package works without Bitrix. Bitrix-specific code lives behind interfaces with a pluggable adapter pattern.

### Current problem

```
Domain\Event\Event
  → new GetCurrentUser()
    → new UserManager()
      → new UserRepository()  ← Bitrix\...\UserRepository (fatal if no Bitrix)
```

Additionally, `UserManager` directly calls `CUser`, `CGroup`, and `CurrentUser` from Bitrix.

### Strategy: Static Factory + Interface (no DI container required)

The pattern that works across Bitrix (no DI), Symfony (has DI), and standalone:

```php
// Domain layer — pure interface
namespace Domain\User\Repository;
interface UserRepositoryInterface { ... }

// Infrastructure layer — Bitrix implementation
namespace Infrastructure\Bitrix\User\Repository;
class BitrixUserRepository implements UserRepositoryInterface { ... }

// Bootstrap (in consuming project's entry point)
UserManager::setRepositoryFactory(fn() => new BitrixUserRepository());
```

This keeps the Domain clean, requires no container, and each framework integration sets up its factories during bootstrap.

### 5a — Prep release

- **Move `Domain\User\Infrastructure\Repository\*` to `Infrastructure\User\Repository\`** — these are infrastructure concerns living in the Domain layer
- **Move `Domain\File\Infrastructure\Repository\*` to `Infrastructure\File\Repository\`**
- Keep old namespaces as deprecated aliases (class files with `class_alias` or `extends`)
- **Introduce repository factory pattern** on managers:
  ```php
  abstract class AbstractManager
  {
      private static array $factories = [];

      public static function setRepositoryFactory(string $managerClass, callable $factory): void
      {
          self::$factories[$managerClass] = $factory;
      }

      protected function createRepository(): RepositoryInterface
      {
          $factory = self::$factories[static::class]
              ?? throw new \RuntimeException(static::class . ' repository factory not configured. Call ' . static::class . '::setRepositoryFactory() during bootstrap.');

          return $factory();
      }
  }
  ```
- **Extract Bitrix-specific methods** from `UserManager` into the repository:
  - `getUserGroup()` (calls `CUser::GetUserGroup`) → `UserRepositoryInterface::getUserGroups(int $userId): array`
  - `getRoles()` (calls `CGroup::GetByID`) → `UserRepositoryInterface::getUserRoles(int $userId): array`
  - `authorize()` (calls `new CUser()->Authorize()`) → new `AuthServiceInterface::authorize(int $userId): void`
  - `getCurrent()` (calls `CurrentUser::get()`) → `UserRepositoryInterface::getCurrentUser(): ?UserInterface`
- **Ship CLI migration script** (`tools/migrate-bitrix-decoupling.php`):
  - Scans consumer code for direct Bitrix imports in Domain layer
  - Reports `new UserRepository()` / `new GroupRepository()` / `new FileRepository()` instantiations that should use the factory
  - Reports old `Domain\*\Infrastructure\Repository\*` namespaces that should be updated
- **Ship PHPStan rule**: detect any `use Bitrix\...` or `use CUser` or `use CGroup` statement in `Domain\` namespace
- **Document**: bootstrap examples for Bitrix, Symfony, and standalone projects

### 5b — Breaking release

- `UserManager`, `GroupManager`, `FileManager` no longer have fallback `new *Repository()` — factory must be configured
- All Bitrix-specific code removed from classes under `Domain\` namespace
- Old `Domain\*\Infrastructure\` namespace aliases removed
- `Event` no longer instantiates `GetCurrentUser` — user provider must be configured
- Bitrix-specific implementations remain in the separate [`foreline/clean-bitrix`](https://github.com/foreline/clean-bitrix) package

---

## Phase 6 — Encapsulation & Quality

**Goal**: Fix public property exposure, raise PHPStan further, improve test coverage.

### 6a — Single release (non-breaking possible with `__get`/`__set` bridge)

- **Make `AbstractValueObjectManager` properties private** (`$filter`, `$sort`, `$limit`, `$fields`, `$group`)
- Add `getFilter(): FilterInterface`, `getSort(): SortInterface`, etc.
- Temporarily support `__get()` for backward compatibility with deprecation warning
- **Raise PHPStan to level 5**
- **Add `@covers` annotations** to all existing tests
- **Increase test coverage** to 80%+ on Domain layer

### 6b — Breaking release

- Remove `__get()` bridge — only accessors
- PHPStan level 5 enforced

---

## Non-Breaking Improvements (Any Release)

These can be shipped at any time without migration tooling:

| Item | Priority | Notes |
|------|----------|-------|
| Translate Russian comments/exceptions to English | Low | Ongoing, no migration needed |
| Implement `Email` value object | Low | New class, no breaking change |
| Remove empty repository subclasses (e.g. `UserSort extends Sort`) | Low | If unused externally |
| Add PHP-CS-Fixer config | Low | Enforces coding standards |
| Add pre-commit hooks (PHPStan + PHPUnit) | Low | Developer workflow improvement |

---

## Migration Tools Summary

Every prep release includes tools in `tools/` directory:

| Tool | Phase | Purpose |
|------|-------|---------|
| `tools/phpstan/FluentReturnTypeRule.php` | 1a | Detects inconsistent fluent setter return types |
| `tools/migrate-return-types.php` | 1a | Scans consumer code for return type issues |
| `tools/phpstan/DeprecatedEntityMethodsRule.php` | 2a | Detects deprecated AbstractEntity method usage |
| `tools/migrate-entity-urls.php` | 2a | Reports URL-related method usage to migrate |
| `tools/phpstan/DeprecatedApiRule.php` | 3a | Detects all deprecated API usage |
| `tools/migrate-deprecated-api.php` | 3a | Automated old → new API renaming |
| `tools/phpstan/DomainBitrixImportRule.php` | 5a | Detects Bitrix imports in Domain layer |
| `tools/migrate-bitrix-decoupling.php` | 5a | Reports direct repository instantiations |

Consumers run: `vendor/bin/phpstan analyse -c vendor/foreline/clean/tools/phpstan/migration.neon`

---

## Release Timeline (Relative)

```
v1.x       v2.0    v2.x       v3.0    v3.x       v4.0
Phase 1a ──► 1b ──► Phase 2a ──► 2b ──► Phase 3a ──► 3b
                                            │
                                      v4.x  │    v5.0    v5.x       v6.0    v6.x       v7.0
                                      Phase 4a ──► 4b ──► Phase 5a ──► 5b ──► Phase 6a ──► 6b
```

- Phases 1–3 are sequential (each depends on the previous)
- Phases 4 and 3 can run in parallel (event system is independent of deprecated API removal)
- Phase 5 depends on Phase 4 (the Event/UserProvider refactor is a prerequisite)
- Phase 6 can start after Phase 5

---

## Resolved Decisions

1. **`aggregatedCount` on `AbstractEntity`**: Stays on `AbstractEntity` for now. A separate issue will evaluate moving it to `CollectionInterface` or a dedicated `AggregatedResult` wrapper in a future phase.
2. **Semver boundary**: Each breaking phase gets its own major version (`2.0.0` through `7.0.0`). This protects consumers from large batches of breaking changes.
3. **Bitrix repository implementations**: Live in the separate [`foreline/clean-bitrix`](https://github.com/foreline/clean-bitrix) package. This repo contains only framework-agnostic code.
4. **`Publisher`/`EventStore` singletons**: Remain singletons. Too many consumers rely on `Publisher::getInstance()`. Refactoring to non-singleton is out of scope for this roadmap.
