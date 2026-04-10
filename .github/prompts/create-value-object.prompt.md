---
description: 'Create a new typed value object following framework conventions'
mode: 'agent'
tools:
  - create_file
  - read_file
  - semantic_search
---

# Create Value Object

Generate a new value object class in the `Domain\ValueObject` namespace.

## Steps

1. Determine the value object name and underlying type (string, int, float, enum, array).
2. Choose the correct interface to implement:
   - String → `StringValueObjectInterface`
   - Int → `IntValueObjectInterface`
   - Float → `FloatValueObjectInterface`
   - Enum → `EnumValueObjectInterface`
   - Array → `ArrayValueObjectInterface`
3. If complex, create a subdirectory: `src/Domain/ValueObject/{Name}/`.
4. Value objects must be immutable — no setters, all state set in constructor.
5. Include validation in the constructor using `webmozart/assert` or exceptions.
6. Add `__toString()` for string-representable VOs.
7. Add `equals(ValueObjectInterface $other): bool` for comparison.
8. Generate a PHPUnit test in `tests/ValueObject/{Name}/`.
