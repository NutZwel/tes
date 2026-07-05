---
name: feature-addition
description: "Use when adding a new feature, new module, new endpoint, or new database table to the live project."
---

# Feature Addition Protocol

> Adding features to a live system is higher risk than building from scratch.
> An existing user base depends on current behavior. Follow this protocol fully.

## 21.1. Pre-Implementation Gate

```text
GATE CHECK — all must be YES to proceed:

  [ ] Is this feature within the system's stated purpose? (§1 — not a Non-Goal)
      Example: Adding song search → NO — search is explicitly out-of-scope (§1.1)
  [ ] Does this feature require a new dependency?
      If YES → evaluate against §2 forbidden alternatives and §24 supply chain rules
      Get explicit approval before adding any new package (N/A — no package manager)
  [ ] Does this feature touch the database schema?
      If YES → plan the migration path now, before coding (§17.4, §21.3)
  [ ] Does this feature change any existing API contract?
      If YES → a breaking change → requires versioning or deprecation plan (§21.4)
  [ ] Does this feature introduce a new attack surface?
      If YES → threat model update required (§18.1) before implementation

If any gate fails or is unclear, STOP and ask — do not guess (§1 header rule).
```

## 21.2. Feature Flag Strategy

```text
RULE: Any feature that is not fully ready for all users at launch MUST be
      gated behind a feature flag. Do not ship half-complete features to production.

Feature flag implementation : N/A — academic project, no feature flags required
Flag naming convention      : N/A
Default value               : N/A

Removal rule: Once a feature is fully rolled out and stable, remove the flag.
              Dead flag code is technical debt — do not leave it indefinitely.
              (N/A — no flags for this project)
```

## 21.3. Database Migration Safety for Live Systems

```text
Live systems have data. These rules are stricter than §17.4 for that reason.

FORBIDDEN on live systems (even with a rollback plan):
  - Dropping a column that is still referenced in deployed code
  - Renaming a column or table without a two-phase migration
  - Adding a NOT NULL column without a DEFAULT or a data backfill first
  - Any migration that locks a high-traffic table for > 5 seconds

REQUIRED two-phase process for breaking schema changes:
  Phase 1 (deploy): Add new column/table. Deploy code that writes to both old and new.
  Phase 2 (next deploy): Remove old column/table. Deploy code that reads only from new.
  Never combine Phase 1 and Phase 2 into a single deployment.

Migration testing: Every migration MUST be tested against a copy of production data
  volume in staging before running in production.
  (N/A — academic local only, test on fresh XAMPP install)
```

## 21.4. API Backward Compatibility

```text
RULE: Existing API consumers must not break when a new version is deployed.

BACKWARD-COMPATIBLE changes (safe — no versioning needed):
  - Adding a new optional field to a response body
  - Adding a new optional query parameter
  - Adding a new endpoint
  - Relaxing validation (accepting more inputs)

BREAKING changes (require versioning or deprecation process):
  - Removing a field from a response
  - Changing a field's type or format
  - Making an optional field required
  - Removing or renaming an endpoint
  - Tightening validation (rejecting inputs that were previously accepted)

Versioning strategy : N/A — no API versioning for academic MPA
Deprecation notice  : N/A
Deprecation period  : N/A
```
