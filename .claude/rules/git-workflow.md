# Git & Repository Rules — Laufey (Music Player and Downloader)

## Commit Convention (Conventional Commits — MANDATORY)

```
Format: <type>(<scope>): <short description>

Types:
  feat     : New feature
  fix      : Bug fix
  refactor : Code change that neither fixes a bug nor adds a feature
  perf     : Performance improvement
  test     : Adding or correcting tests
  docs     : Documentation only
  chore    : Build process, dependency updates, tooling
  ci       : CI/CD configuration changes

Scope    : [FEATURE_NAME or DIRECTORY, e.g., auth, catalog, admin-songs, player]
Subject  : Imperative mood, lowercase, no period. Max 72 chars.

Examples:
  feat(catalog): add paginated song list
  fix(player): enforce guest play limit server-side
  refactor(admin-songs): extract MIME validation into helper
  test(song-model): add pagination unit test

FORBIDDEN commit messages:
  "fix stuff", "WIP", "changes", "update", "final", "done"
  These will be rejected at code review.
```

## Branch Strategy

```
Main branches:
  main       : Production-ready code only. Direct pushes FORBIDDEN.
  dev        : Integration branch. All feature branches merge here first.

Short-lived branches:
  feat/[TICKET-ID]-[short-description]   e.g., feat/PROJ-42-add-playlist-module
  fix/[TICKET-ID]-[short-description]    e.g., fix/PROJ-71-guest-download-403
  chore/[short-description]              e.g., chore/upgrade-bootstrap-5.3

Rules:
  - Branch off from   : dev (features/fixes) | main (hotfixes only)
  - Merge back into   : dev first → main via release PR
  - Delete after merge: YES — no stale branches
  - Rebase or merge   : merge — CI3 is not framework-sensitive to rebase
```

## .gitignore Requirements

```
MUST ignore (minimum):
  .env, .env.local, .env.*.local
  application/logs/
  protected_uploads/  # audio and cover files — NEVER committed
  *.log, *.local
  agent_render_v*.png  # N/A but include for consistency
  .DS_Store, .idea/, .vscode/settings.json
  *.sqlite, prisma/dev.db  # N/A but include for consistency
  coverage/, .nyc_output/  # N/A but include for consistency
```
