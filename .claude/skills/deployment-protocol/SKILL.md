---
name: deployment-protocol
description: "Use when deploying changes, preparing submission artifacts (mysqldump + zip), or managing environment config."
---

# Deployment Protocol

## §9.2 Environment Promotion

```
Environments: local only (XAMPP) — no staging/production

Rules:
  - Code MUST pass manual browser testing before submission.
  - Database migrations run BEFORE the new application version is deployed (not after).
  - NEVER run migrations directly against production without a tested rollback script ready.
  - Environment-specific config is injected via config.php only — no env-specific code branches.
```

## §9.3 Build & Artifact Rules

```
- Build command  : N/A — CI3 is PHP-based, no build step
- Output dir     : N/A
- Build MUST be reproducible (same input → same output, no timestamp-dependent artifacts).
- Build artifacts are NEVER committed to Git.
- Bundle size limit (if applicable): N/A — Bootstrap 5.3 + vanilla JS only, no bundler
[NO CONTAINERIZATION] — XAMPP local only, no Docker required
```

## §13 STEP 8 — Final Build & Deployment

```
STEP 8 — Final Build & Deployment
  8.1  Export database via mysqldump: laufey_db.sql
  8.2  Archive project folder (excluding protected_uploads/) → laufey.zip
  8.3  Submit .sql + .zip to professor
  Done when: Submission artifacts are ready and verified restorable on fresh XAMPP.
```
