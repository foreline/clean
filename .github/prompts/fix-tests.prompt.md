---
description: 'Run failing tests, diagnose errors, and fix them'
mode: 'agent'
tools:
  - run_in_terminal
  - read_file
  - replace_string_in_file
  - grep_search
---

# Fix Failing Tests

Diagnose and fix failing PHPUnit tests.

## Steps

1. Run `vendor/bin/phpunit` and capture the output.
2. Parse each failure: identify the test method, the assertion that failed, and the actual vs expected values.
3. Read the failing test file and the source file under test.
4. Determine root cause:
   - Is the test wrong (outdated assertion, wrong mock setup)?
   - Is the source code wrong (regression, missing logic)?
5. Fix the root cause. Prefer fixing source code bugs over weakening tests.
6. Re-run `vendor/bin/phpunit --filter {FailingTestClass}` to confirm the fix.
7. Run the full suite once all targeted fixes are applied.
