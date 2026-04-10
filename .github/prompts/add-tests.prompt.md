---
description: 'Generate PHPUnit tests for a class or file'
mode: 'agent'
tools:
  - read_file
  - create_file
  - grep_search
  - semantic_search
  - run_in_terminal
---

# Add Tests

Generate comprehensive PHPUnit tests for the specified class or file.

## Steps

1. Read the target source file to understand its public API.
2. Identify the correct test directory mirroring the `src/` path under `tests/`.
3. Create a test class named `{ClassName}Test` extending `PHPUnit\Framework\TestCase`.
4. For each public method, generate at least:
   - A happy-path test
   - An edge-case test (empty input, null, boundary values)
   - An error/exception test if the method throws
5. Use AAA pattern with `// Arrange`, `// Act`, `// Assert` comments.
6. Use `createMock()` for interface dependencies; inject via `setUp()`.
7. Use descriptive assertion messages.
8. Run `vendor/bin/phpunit --filter {TestClass}` to verify tests pass.
