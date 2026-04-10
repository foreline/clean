---
description: 'Scaffold a new domain entity with aggregate, value objects, and repository interface'
mode: 'agent'
tools:
  - create_file
  - read_file
  - semantic_search
  - grep_search
---

# Create Entity

Generate a complete domain entity following Clean Architecture conventions.

## Steps

1. Ask for the entity name and its properties (name, type, nullable, default value).
2. Create the entity class extending `Domain\Entity\AbstractEntity` in `src/Domain/{EntityName}/Aggregate/`.
3. Create the aggregate interface extending `Domain\Aggregate\AggregateInterface`.
4. For each property that qualifies as a value object (email, money, color, IP, etc.), create or reuse existing value objects from `src/Domain/ValueObject/`.
5. Create a `{EntityName}Collection` implementing `Domain\Aggregate\CollectionInterface` with `CollectionTrait`.
6. Create a repository interface in `src/Domain/{EntityName}/Repository/` extending `Domain\Repository\RepositoryInterface`.
7. Following conventions:
   - `declare(strict_types=1)` in every file
   - PHPDoc on all methods
   - Fluent setters returning `self`
   - Yoda conditions in validation
   - Appropriate use of `webmozart/assert` for validation
8. Generate a basic PHPUnit test for the entity in `tests/Domain/{EntityName}/`.
