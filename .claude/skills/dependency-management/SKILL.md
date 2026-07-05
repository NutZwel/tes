---
name: dependency-management
description: "Use when updating dependencies, evaluating a new library, responding to a security advisory, or managing EOL packages."
---

## §24. Dependency Lifecycle Management

### §24.1. Update Cadence

```
PATCH versions  (x.x.N) : Apply within 1 week — low risk, do not defer
MINOR versions  (x.N.x) : N/A — no npm/pip dependencies in this project
MAJOR versions  (N.x.x) : N/A — no package manager

Audit schedule:
  [ ] N/A — no npm audit for PHP-only project
  [ ] Weekly: manual review of CI3 security advisories (codeigniter.io)
  [ ] Quarterly: check for Bootstrap/jQuery versions approaching EOL

CRITICAL advisory (CVSS >= 9.0): Treat as P0 — patch immediately regardless of cadence.
HIGH advisory (CVSS 7.0-8.9)  : Treat as P1 — patch within 7 days.
```

### §24.2. End-of-Life (EOL) Library Policy

```
A library is considered EOL if any of the following are true:
  - The maintainer has publicly announced end of support
  - No commit activity for > 18 months AND no response to open security issues
  - The runtime it depends on (PHP 8.2, MySQL 8.0, Apache) is itself EOL

EOL response process:
  STEP 1 — Identify replacement candidate. Evaluate against §2 criteria.
           e.g., CI3 -> CI4 (but CI4 forbidden by academic constraint — must stay CI3)
  STEP 2 — Estimate migration effort. If > 2 days: schedule as a dedicated task.
           N/A — CI3 cannot be replaced due to academic mandate
  STEP 3 : N/A — no migration possible for CI3
  STEP 4 : N/A — §2 Tech Stack cannot be updated for CI3
  Deadline: EOL libraries must be replaced within 90 days of identification.
            EXCEPTION: CI3 is EOL but mandated — document this exception in README.md
```

### §24.3. Adding New Dependencies

```
Before installing any new package, the agent MUST verify:

  [ ] Is the functionality already available in the current stack? (§2)
      e.g., jQuery already provided — do NOT add Axios
      If yes: use what exists. Do not add a package for convenience.
  [ ] Is this package actively maintained?
      Signal: commits in last 6 months, issues being responded to
  [ ] Weekly download count > 50,000? (proxy for ecosystem stability)
      N/A — PHP libraries not measured by downloads
  [ ] Does it have an open CRITICAL or HIGH unpatched advisory? (§18.7)
      If yes: do not install until patched.
  [ ] Does it have postinstall scripts?
      If yes: read the script before approving — it executes with local permissions (§18.7)
      N/A — no npm postinstall scripts
  [ ] Is it a runtime dependency or dev-only?
      Misclassifying a dev dependency as runtime increases attack surface.
      N/A — no package manager

New dependency approval: Requires explicit user confirmation before adding any PHP library.
After adding: update §2 Tech Stack with name, version, and rationale.
```
