---
name: bug-triage
description: "Use when responding to a bug report, handling a P0/P1/P2/P3 incident, creating a hotfix branch, or performing a rollback."
---

# Bug Triage & Hotfix Protocol

## 22.1. Severity Classification

```text
P0 — CRITICAL (respond within 1 hour)
  Definition : System is down, data loss is occurring, or a security breach is active.
  Examples   : XAMPP Apache 500-ing on all requests. MySQL unreachable. Auth bypass confirmed.
  Process    : → Immediately notify professor (on-call contact)
               → Assess: can a rollback resolve it? (§22.3)
               → If rollback resolves it: rollback first, fix later
               → If not: hotfix branch from main → fix → verify → submit

P1 — HIGH (respond within 24 hours)
  Definition : Core feature is broken for all or most users. No acceptable workaround.
  Examples   : Login endpoint returning 500. Download flow failing. Data not persisting.
  Process    : → Hotfix branch from main (§12.2)
               → Fix, test manually, deploy within SLA
               → Postmortem required if user data was affected

P2 — MEDIUM (fix in next planned release)
  Definition : Feature is degraded but a workaround exists. Non-critical data inconsistency.
  Examples   : Slow query causing timeout on edge case input. Pagination off by one.
  Process    : → File tracked issue with reproduction steps
               → Fix in normal feature branch from dev (§12.2)
               → No hotfix branch — normal review process

P3 — LOW (backlog — fix when prioritized)
  Definition : Minor visual or UX issue. Edge case with negligible user impact.
  Examples   : Error message wording incorrect. Tooltip misaligned.
  Process    : → File tracked issue
               → No urgency on resolution
```

## 22.2. Hotfix Execution Rules

```text
A hotfix branch is ONLY created for P0 and P1 issues.

Branch naming : fix/[TICKET-ID]-[short-description]-hotfix  (§12.2)
Branch from   : main — NOT dev (hotfix must go to production, not wait for dev cycle)
Merge into    : main first → then backport to dev

Hotfix checklist:
  [ ] Reproduction case confirmed and documented
  [ ] Root cause identified — not just symptoms patched
  [ ] Fix is minimal — only what's needed to resolve the issue
  [ ] Manual browser test suite passes (all UCs)
  [ ] New test case added that fails before the fix and passes after
  [ ] Verified on fresh XAMPP install before submission

FORBIDDEN during hotfix:
  - Combining the hotfix with unrelated improvements ("while I'm here...")
  - Skipping the fresh XAMPP verification step, regardless of urgency
  - Submitting without a rollback plan ready
```

## 22.3. Rollback Decision Criteria

```text
ROLL BACK IMMEDIATELY if any of the following are true:
  - The last commit correlates with the start of the incident (check git log timestamps)
  - Error rate > 5% of requests after a commit (manual browser test failures)
  - P0 and a working prior version exists in git history
  - Database schema change in the commit has a ready rollback and no data loss has occurred

DO NOT roll back if:
  - The bug predates the last commit (rollback won't help)
  - The migration has already written data that the old code cannot read
    → In this case, forward-fix only. Rolling back would corrupt data.

Rollback command : git checkout main@{previous-commit} && git reset --hard HEAD
Post-rollback    : Verify http://localhost/laufey/ returns 200. Notify professor. Begin root cause analysis.
```

## 22.4. Post-Mortem Requirements

```text
Required for: All P0 incidents. All P1 incidents that affected user data.
Due          : Within 48 hours of resolution.
Location     : docs/postmortems/YYYY-MM-DD-short-title.md

Post-mortem MUST contain:
  1. Timeline       — when was it detected, who detected it, when was it resolved
  2. Root Cause     — the actual technical cause, not "human error"
  3. Impact         — number of UCs affected, data affected, duration
  4. Resolution     — exactly what fixed it
  5. Prevention     — specific code, test, or process change that
                      makes this class of issue impossible or detectable earlier
                      (Vague actions like "be more careful" are not acceptable)
```
