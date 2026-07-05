---
name: post-launch-checklist
description: "Use when preparing for submission, performing go-live verification, running pre-deployment checks, or verifying the system is stable."
---

# Post-Launch Checklist

This checklist is a one-time gate. All items must be verified and checked off before the system is considered "live and stable." Do not begin feature work until every item below is confirmed.

## INFRASTRUCTURE

- [ ] `http://localhost/laufey/` returns 200 in XAMPP
- [ ] All `config.php` values validated at startup (§16.1)
- [ ] `protected_uploads/` is outside web root (above `htdocs/`)
- [ ] `schema.sql` imported cleanly via phpMyAdmin
- [ ] Build artifacts excluded from git (§12.3) — `protected_uploads/` ignored

## MONITORING & OBSERVABILITY

- [ ] N/A — no uptime monitor for local XAMPP
- [ ] N/A — no error rate alert for local only
- [ ] N/A — no latency alert for local only
- [ ] Log files present in `application/logs/`
- [ ] Database connection pool monitored — N/A — MySQL default pool

## SECURITY

- [ ] CSRF protection enabled in `config.php` (`$config['csrf_protection'] = TRUE`)
- [ ] `finfo_file()` MIME validation confirmed on admin upload (§18.3)
- [ ] IDOR checks verified on playlists/favorites/download (§18.2)
- [ ] `.env` not committed — verified via `git log --all --full-history -- .env`
- [ ] Admin routes (`/admin/*`) not publicly accessible (`role='admin'` check)

## DATA INTEGRITY

- [ ] Database schema imported cleanly with zero errors
- [ ] Seed data (admin user) matches expected baseline (`seed_admin.sql`)
- [ ] Backup restoration tested — `mysqldump laufey > backup.sql`, re-import verified

## RUNBOOK

- [ ] Rollback procedure documented — drop tables, re-import `schema.sql` previous version
- [ ] On-call escalation path defined: N/A — student developer only
- [ ] Known limitations documented in `README.md` (no search, no LRC karaoke, no email verification)
