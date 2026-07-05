# Claude: Read this file when managing production changes, reviewing monitoring, or performing database maintenance.

# Maintenance Mode: Operational Rules

## 20.1. Change Control

**RULE:** No change — however small — is deployed to production without passing the full test suite and a code review. "Small" is not an exception category.

**Production change window:** N/A — academic local only, no deployment

**Prohibited periods:** N/A

**Emergency exception:** N/A — P0 incidents only (professor submission deadline)

### Pre-deploy Checklist (every deploy)

- [ ] All manual browser tests pass (all UCs)
- [ ] Lint clean (`php -l` on all controllers/models)
- [ ] Migration (schema.sql update) has a documented rollback
- [ ] Changelog entry written
- [ ] Rollback command ready (drop tables + re-import schema.sql)

---

## 20.2. Monitoring Review Cadence

| Cadence   | Status |
|-----------|--------|
| DAILY     | N/A — no production logs for local only |
| WEEKLY    | N/A — no dependency audit for PHP-only project |
| MONTHLY   | N/A — no resource utilization review for local only |
| QUARTERLY | N/A — no security review for academic local project |

---

## 20.3. Database Operational Rules

- NEVER run manual SQL against production outside of schema.sql (N/A — local only)
- NEVER run migrations without a tested rollback script ready (rollback = drop + re-import)
- Backup verification: restore a backup to staging (phpMyAdmin) once before submission
- Query monitoring: flag any query exceeding 1000ms in phpMyAdmin — treat as bug and fix
- Connection pool exhaustion: N/A — MySQL default pool for local only
