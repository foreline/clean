---
description: 'Review recent code changes for bugs, security issues, and style violations'
mode: 'agent'
tools:
  - get_changed_files
  - read_file
  - grep_search
  - semantic_search
---

# Code Review

Perform a thorough code review on recent changes.

## Review Checklist

1. **Gather changes**: Use `get_changed_files` to identify modified files, then read each one.
2. **Architecture compliance**: Verify dependency direction (Domain ← Infrastructure ← Presentation). Domain must not import from Infrastructure or Presentation.
3. **Type safety**: Check for missing type hints, use of `mixed` where avoidable, missing `strict_types`.
4. **Naming**: Interfaces end with `Interface`, abstract classes start with `Abstract`, traits end with `Trait`.
5. **Value objects**: Ensure immutability — no setters on VOs.
6. **Error handling**: No silenced exceptions, proper use of typed exceptions.
7. **Security**: Check for injection risks, unvalidated input, information leakage.
8. **PHPDoc**: All public/protected methods have complete docblocks.
9. **Tests**: New code should have corresponding tests. Check coverage.
10. **Performance**: Watch for N+1 patterns, unnecessary object creation in loops.

## Output Format

Present findings as:
- **Critical**: Must fix before merge
- **Warning**: Should fix, potential issues
- **Suggestion**: Optional improvements
