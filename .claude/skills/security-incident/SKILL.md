---
name: security-incident
description: "Use when responding to a security incident, confirmed or suspected unauthorized access, data exposure, or exploit."
---

# §23 Security Incident Response

> An incident is any confirmed or suspected unauthorized access, data exposure,
> or exploitation of a system vulnerability.

## §23.1 Detection Signals

Treat ANY of these as a potential security incident until proven otherwise:

  - Unusual spike in 401/403 errors (credential stuffing or auth bypass attempt)
  - Requests to /admin/* endpoints from non-admin sessions
  - Database query volume spike not correlated with browser traffic
  - User-reported: "I can see another user's playlist/favorites"
  - Unexpected admin account creation in logs
  - Unusual file uploads (non-audio files accepted as .mp3)

## §23.2 Immediate Containment Steps

Upon confirming or strongly suspecting an incident, execute in order:

**STEP 1 -- Contain (within minutes)**
  - If active breach: take XAMPP Apache offline (stop module) or enable maintenance mode
  - Rotate all compromised or potentially compromised secrets immediately (see §18.4)
    e.g., encryption_key in config.php
  - Revoke active sessions if auth tokens are suspected compromised (clear CI3 session data)
  - Block the attacking IP range at XAMPP level if identifiable (.htaccess deny from)

**STEP 2 -- Preserve Evidence (do not destroy before this)**
  - Export and preserve application/logs/ BEFORE rotating secrets or restarting Apache
  - Snapshot the database at the current state if data manipulation is suspected
  - Do NOT modify the XAMPP environment in ways that destroy forensic evidence

**STEP 3 -- Assess Scope**
  - What data was potentially accessed? (see §18.1 threat model is the starting point)
  - What is the earliest possible time of compromise? (check logs back to last clean audit)
  - Is the vulnerability still exploitable? If yes, containment is not complete.

**STEP 4 -- Notify**
  Notify professor immediately.
  If user PII was involved: legal and compliance review required before any public statement.
  Regulatory disclosure timeline: N/A -- academic local only, no regulatory requirement

## §23.3 Recovery Sequence

ONLY proceed to recovery after containment is confirmed complete.

  1. Patch the exploited vulnerability (follow P0 hotfix process -- see §22.2)
  2. Re-audit all code paths adjacent to the vulnerability
  3. Redeploy from a verified clean build -- do not patch in-place on XAMPP
  4. Verify all secrets have been rotated and old values are invalidated
  5. Restore from backup ONLY if data integrity is in doubt -- verify backup is pre-incident
  6. Re-run full security checklist from §18 before bringing back online
  7. Monitor at elevated frequency for 72 hours post-recovery (manual browser tests)

## §23.4 Post-Incident Requirements

Post-mortem required: YES -- for every confirmed security incident (see §22.4 format)
  Additionally include:
    - Vulnerability class (OWASP category or CVE if applicable)
    - Whether the vulnerability was present in code, config, or dependency
    - Whether existing security controls (see §18) would have caught this -- if not, why not

Threat model update: Revise §18.1 if the incident revealed a previously unlisted threat.

Disclosure policy: N/A -- academic local only, no public disclosure required
