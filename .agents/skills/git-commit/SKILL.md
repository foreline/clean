---
name: git-commit
description: "Commit changes using Conventional Commits"
tools: ['Bash', 'Read', 'Grep', 'Edit', 'Write']
---

# Git Commit

Commit assistant for the **Pristine Framework** (`foreline/clean`) project — a PHP
framework for building applications using Clean Architecture / DDD principles.
Three duties, in order: conventional commit message → brief changelog entry →
**one** commit containing the staged changes **and** the changelog update.

## Speed rules (follow strictly)

1. Run all git commands in the repo root (`D:/github/clean`).
2. Stage **everything first**, including untracked files:
   - `git ls-files --others --exclude-standard` (metadata only — list, don't open)
   - `git add -A`
   - If both the staged diff and the untracked list are empty, exit and report
     "nothing to commit".
3. Inspect staged changes with **metadata only**:
   - `git diff --cached --name-status`
   - `git diff --cached --stat`
   - Never run `git diff --cached` (full content).
4. Read content only of small source-of-truth files when needed to explain the why.
5. Do not run tests, linters, or builds.
6. **Never** create a separate commit for the changelog (no `chore: update changelog`,
   no `docs: changelog`). The changelog is always part of the main commit.

## Grouping

- One logical change = one commit.
- Multiple **unrelated** changes (different features/issues) → split into separate
  commits by area. Do not create one giant mixed commit.
- **Dependency updates** (`composer.lock`, `composer.json`) MUST be in their own
  `chore(deps): ...` commit, separate from code or config changes. In the commit
  body, list meaningful package transitions (e.g. `symfony/messenger v7.4.0 → v7.4.1`).
- To split, commit with pathspecs: `git commit -m "<msg>" -- <paths>` — always
  including `CHANGELOG.md` in each commit's pathspec (update it per commit).
- Execute the planned commit(s) immediately, without asking for confirmation.

## Scope Detection

Derive scopes from the project structure:

- Top-level layers under `src/`: `domain`, `infrastructure`, `presentation`.
- Domain sub-areas: `entity`, `aggregate`, `repository`, `event`, `scheduler`, `usecase`, `user`, `file`, `valueobject`, `lifecycle`, `subscriber`.
- Infrastructure sub-areas: `di`, `mailer`, `migration`, `event`, `helpers`.
- Presentation sub-areas: `http`, `form`, `response`, `helpers`.
- Universal scopes: `docs`, `tests`, `config`, `ci`, `deps`.

Keep scopes short, lowercase, and consistent.

## Conventional Commits Format

```
<type>(<scope>): <imperative summary in English, max 72 chars>

<optional body: what and why, wrap at 100 chars>
```

### Types

- `feat` — new functionality
- `fix` — bug fix
- `refactor` — internal restructuring without behavior change
- `docs` — documentation-only changes
- `style` — formatting-only changes
- `chore` — maintenance, tooling, dependencies, housekeeping
- `perf` — performance improvements
- `test` — adding or updating tests

### Rules

- Subject line in English, imperative mood, max 72 characters.
- Body in English, concise — explain what and why, not implementation detail.
- Add issue references when available: `Refs: #123`.

### Good Examples

```
chore(deps): update symfony/messenger to v7.4.1

symfony/messenger v7.4.0 → v7.4.1 (patch release).
```

```
feat(event): add retry policy for async event dispatching

Implement per-subscriber RetryPolicy with exponential backoff for
failed event dispatches in SymfonyMessengerAsyncDispatcher.
```

```
fix(repository): prevent duplicate field mapping in filter

Skip fields already registered in the type map to avoid query errors.
```

## Changelog (mandatory, brief)

File: `CHANGELOG.md` in the project root. If missing, create it with a `# Changelog`
header. Simple list format, newest on top:

```markdown
# Changelog

## 2026-09-20

- feat(asset): add salvage value to DeprecationGroup
- fix(ticket): resolve nullable assignee hydration
```

Rules:
- One bullet per commit in this run, mirroring the commit subject line verbatim.
- Reuse today's `## YYYY-MM-DD` heading if it already exists; otherwise add a new
  heading directly under `# Changelog`.
- Entries describe **what changed**, briefly — no bodies, no detail sections.
- Leave pre-existing legacy sections (e.g. Keep-a-Changelog `[Unreleased]` blocks)
  untouched; new entries always go into the dated list on top.

## SemVer Tagging

After committing, autonomously decide whether to create a new version tag. Create an
**annotated git tag** following [Semantic Versioning 2.0.0](https://semver.org/).

### When to Tag

Use your judgment. Create a tag when any of these apply:
- A meaningful `feat` commit introduces new user-facing functionality.
- A critical `fix` addresses an important bug.
- A significant dependency update affects public API behavior.
- Multiple smaller changes have accumulated since the last tag forming a coherent release.
- The user explicitly requests a tag or release.

Do **not** tag for:
- Trivial or internal-only changes (`chore`, `docs`, `style`, `ci`, `test`) that don't affect public API, unless the user requests it.
- Work-in-progress or partial features.

### Determining the Next Version

1. Find the latest tag: `git describe --tags --abbrev=0`.
2. Review commits since that tag: `git log <latest-tag>..HEAD --oneline`.
3. Classify changes using Conventional Commits types:
   - Any commit with a `BREAKING CHANGE` footer or `!` after the type → bump **MAJOR**.
   - Any `feat` commit → bump **MINOR** (unless MAJOR is already bumped).
   - Significant dependency update affecting public API → bump **MINOR**.
   - Only `fix`, `refactor`, `perf`, `docs`, `style`, `chore`, `test` with no API changes → bump **PATCH**.
4. If the user specifies a version explicitly, use that instead.

### Creating the Tag

```bash
git tag -a v2.0.0 -m "v2.0.0: <short summary of release>"
```

Tag message format — one-line summary plus commit subjects since the previous tag,
grouped by type:

```
v2.0.0: <one-line summary>

Changes:
- feat(scope): description
- fix(scope): description
- chore(deps): description
```

### Rules

- Always prefix versions with `v` (e.g., `v1.0.0`, not `1.0.0`).
- Never tag uncommitted or dirty state — all changes must be committed first.
- Tagging does NOT rename or restructure `CHANGELOG.md`; the dated list stays as-is.
- Do not push the tag automatically — let the user decide when to push
  (release pushes go to **all** remotes: `git push github main --tags && git push gitlab main --tags`).
- Pre-release versions use a hyphen suffix: `v1.2.0-alpha.1`, `v1.2.0-beta.2`, `v1.2.0-rc.1`.

## User Input

$ARGUMENTS
