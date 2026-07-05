# Code Quality Rules — Laufey (Music Player and Downloader)

## Layer Responsibilities

### Interface Layer (Controllers, Views)

```
Responsibility : Parse and validate incoming input. Call model functions.
                 Format and return output (HTML or JSON). Nothing else.
Forbidden      : No business logic. No direct database calls. No raw queries.
```

### Service Layer (N/A — CI3 models contain both service and repository logic)

```
Responsibility : All business rules, domain decisions, and orchestration.
Forbidden      : No HTTP/framework-specific imports. No direct database calls.
                 Must be callable from both a controller route and a test with no mocking required.
```

### Repository Layer (Models)

```
Responsibility : All database queries, reads, and writes. Return plain data objects.
Forbidden      : No business logic. No HTTP imports. No transformation beyond
                 column-to-field mapping.
```

### Schema Layer (schema.sql)

```
Responsibility : Define data shapes and validation rules used across layers.
Forbidden      : No runtime side effects. No imports from service or repository layers.
```

## Code Quality Constraints

- Strict typing is MANDATORY for JavaScript (player.js) — no implicit any.
- Maximum function length    : 40 lines — extract if longer
- Maximum file length        : 300 lines — split into modules if longer
- Cyclomatic complexity limit: max 10 per function
- No commented-out code in commits. Use git history for reference.
- No TODO comments in merged code. File a tracked issue instead.

Linter   : PHP linter (php -l)
Formatter: Manual — follow CI3 coding style
Pre-commit hooks: N/A — academic project, no CI pipeline
  Hooks run: php -l on all changed controllers/models
  Commits that fail syntax MUST NOT be pushed.
