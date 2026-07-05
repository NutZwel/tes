# CLAUDE.md — Laufey (Music Player and Downloader)

> **Important**: This file is the single source of truth for all agents working on this project. Instructions here override general best practices when they conflict. Do not make assumptions outside of what is defined here. If a situation is not covered, stop and ask — do not guess.

## 1. Project Overview

- **Name**: Laufey (Music Player and Downloader)
- **Description**: Browser-based web application for streaming and downloading digital audio files, developed under academic constraints: CodeIgniter 3 backend and Bootstrap-only frontend with no JavaScript frameworks.
- **Goal**: Offline-first execution on local XAMPP environment with strict guest access enforcement (3 plays/session, 1 download/day by IP) and registered user unrestricted access.
- **Consumers**: End-users via browser (Guest, Registered User, Admin).
- **Version**: v1.0.0
- **Status**: Active development

### 1.1. Non-Goals (MANDATORY — prevents scope creep)

This system explicitly does NOT: Handle time-synced LRC karaoke — static plain-text lyrics only. Support real-time streaming — use HTML5 Audio for progressive download. Store user PII beyond username/email — no email verification, no password reset. Scale horizontally — single-server XAMPP local deployment only. Implement song/search filter — catalog is paginated only. Provide admin analytics dashboard — play/download stats are out-of-scope. Support social features — no comments, sharing, or public playlists. Integrate external music APIs — catalog is Admin-created only. Process payments or subscriptions — all users are free.

### 1.2. Core Logic Pipeline (CRITICAL)

> NEVER store audio files inside the web root — ALL file serving must go through a CI3 controller that reads the file by database-stored path. Direct URL access to audio files must be architecturally impossible (directory outside htdocs/) or blocked via .htaccess.

> NEVER run schema-altering queries at runtime — ALL schema changes must go through schema.sql only. Do NOT ALTER tables at runtime.

**Phase Summary**: PHASE 1 (Admin Upload) validates MIME via `finfo_file()`, generates randomized filenames, and moves files to `protected_uploads/`. PHASE 2 (Persistence) inserts into `songs` and `lyrics` tables via CI3 Active Record parameterized queries. PHASE 3 (Catalog + Play Limit) serves paginated catalog and enforces guest 3-play/session limit via server-side session counter. PHASE 4 (Download Enforcement) checks guest IP+date limit (1/day) or registered user unlimited, then serves file via `force_download()` using absolute server path — never direct URL.

## 2. Tech Stack

| Layer | Technology | Constraint |
|---|---|---|
| Language | PHP 8.2 | — |
| Framework | CodeIgniter 3.1.13 | NOT CI4 |
| Database | MySQL 8.0 via XAMPP | NOT PostgreSQL/SQLite |
| ORM | CI3 Active Record | No raw SQL concat |
| Frontend | Bootstrap 5.3 + vanilla JS | No React/Vue/Angular |
| Auth | CI3 session (bcrypt) | No OAuth/NextAuth |
| Validation | CI3 Form Validation | — |
| Infrastructure | XAMPP local only | No cloud deploy |

## 3. Commands

```bash
# Dev start: Start Apache + MySQL modules in XAMPP Control Panel, then http://localhost/laufey/
# Lint:     php -l application/controllers/*.php
# DB import: Use phpMyAdmin UI to import schema.sql
# Seed:     mysql -u root -p laufey < seed_admin.sql
# Test:     Manual browser testing — no automated framework
```

## 4. Project Structure

```
laufey/
├── index.php
├── application/
│   ├── config/          # config.php, database.php, routes.php
│   ├── controllers/     # Catalog, Player, Download, Auth, Admin_Song, Admin_User, Playlist, Favorites, User
│   ├── models/          # Song, Lyrics, User, Playlist, Favorites, Download_Logs
│   ├── views/           # catalog, player, login, register, admin_songs, admin_song_add/edit, admin_users, playlist, playlist_detail, favorites
│   └── logs/
├── protected_uploads/   # audio/, covers/ — OUTSIDE web root, NEVER committed
├── public/
│   ├── css/             # bootstrap, jquery, player.js, theme-switcher.js
│   └── images/
├── schema.sql
├── seed_admin.sql
├── .env.example
└── tests/               # manual browser test cases
```

**Import Direction Rule (CRITICAL)**: Controllers → Models → Database. Views render data from controllers only. Models MUST NOT import from controllers. Views MUST NOT call models or database directly. No circular imports.

## Quick Reference — Load These When Needed

| When doing... | Load this |
|---|---|
| Debugging a failure | skill: debug-protocol |
| Adding a new feature | skill: feature-addition |
| Responding to a bug | skill: bug-triage |
| Deploying / building submission | skill: deployment-protocol |
| Security incident | skill: security-incident |
| Writing tests | skill: testing-protocol |
| Building UI from mockup | skill: vision-loop (DISABLED — see skill) |
| Managing dependencies | skill: dependency-management |
| Pre-launch verification | skill: post-launch-checklist |
| Checking project phase | skill: dev-roadmap |
| API contract questions | doc: .claude/docs/api-spec.md |
| Database schema details | doc: .claude/docs/database-config.md |
| Env var list | doc: .claude/docs/env-vars.md |
| Full security config | doc: .claude/docs/security-config.md |
| State management design | doc: .claude/docs/state-architecture.md |
| Performance budgets | doc: .claude/docs/performance-budgets.md |
| Maintenance operations | doc: .claude/docs/maintenance-ops.md |
| Naming conventions | rule: .claude/rules/naming-conventions.md |
| Code quality limits | rule: .claude/rules/code-quality.md |
| Git commits & branches | rule: .claude/rules/git-workflow.md |
| Security hard bans | rule: .claude/rules/security-rules.md |
| Data flow rules | rule: .claude/rules/data-flow-rules.md |
| All DO NOT rules | rule: .claude/rules/do-not.md |

