---
applyTo: 'tests/**'
---

# Test Conventions

- Extend `PHPUnit\Framework\TestCase`
- Name test classes as `{ClassName}Test` in a namespace mirroring `src/` under `Tests\`
- Name test methods as `test{Behavior}` using descriptive camelCase (e.g., `testGetReturnsEmptyArrayInitially`)
- Structure each test with AAA comments: `// Arrange`, `// Act`, `// Assert`
- Use `setUp()` for shared test fixtures; use `tearDown()` if cleanup is needed
- Use assertion messages as the last parameter for clarity
- Prefer `assertSame` over `assertEquals` for strict comparisons
- Use `createMock()` for interface dependencies
- One logical assertion per test when practical
- Do not test private methods directly — test through public API
