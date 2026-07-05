# Claude: Read this file when optimizing performance, configuring resource limits, or evaluating response time budgets.

---

## Performance Budgets & Resource Constraints

**Source:** CLAUDE.md §11 — Optimization & Resource Constraints
**Project:** Laufey (Music Player and Downloader) (XAMPP local, PHP 8.2 + MySQL 8.0 + Apache)
**Last synced:** 2026-06-12

---

## 1. Performance Budgets (§11.1)

### 1.1 Web / Frontend Budgets

| Metric | Target | Notes |
|---|---|---|
| First Contentful Paint (FCP) | < 2.0s | Measured on local XAMPP |
| Time to Interactive (TTI) | < 3.5s | — |
| Bundle size (initial) | < 300KB | Bootstrap 5.3 + jQuery + player.js combined |
| Lighthouse score | > 80 | Across all categories; local only, no CDN |

**Frontend composition:**
- Bootstrap 5.3 CSS (`bootstrap.min.css`)
- Bootstrap 5.3 JS (`bootstrap.min.js`)
- jQuery (`jquery.min.js`)
- `player.js` — vanilla JS HTML5 Audio player (queue, shuffle, loop, volume)
- `theme-switcher.js` — theme toggle AJAX + body class swap

**Bundle size checklist (verify before any dependency addition):**
- [ ] Total CSS < 150KB (Bootstrap 5.3 minified ~60KB + theme files)
- [ ] Total JS < 150KB (Bootstrap ~60KB + jQuery ~30KB + player.js + theme-switcher.js)
- [ ] No additional JavaScript frameworks (React, Vue, Angular — forbidden)
- [ ] No additional CSS frameworks (Materialize, Tailwind — forbidden)

### 1.2 Backend / API Budgets

| Metric | Target | Applies To |
|---|---|---|
| p95 response time — read | < 300ms | `/catalog`, `/login`, `/playlists`, `/favorites` |
| p95 response time — write | < 600ms | `/admin/songs/add`, `/playlists/create`, upload endpoints |
| Max concurrent DB conn | 20 | Enforced via MySQL connection pool config |
| Memory ceiling | 512MB | Per Apache process (XAMPP default) |

**Read endpoint examples:** catalog listing, song detail, user profile, lyrics fetch
**Write endpoint examples:** song upload, playlist create, registration, login, theme change

---

## 2. Resource Management Rules (§11.2)

### 2.1 Database Connections

- Database connections MUST use a pool. Never create a new connection per request.
- Pool config: `min=2`, `max=20`, `idleTimeoutMs=30000` (MySQL default)
- All queries MUST go through CI3 Active Record with parameterized statements
- N+1 queries are FORBIDDEN — use JOINs for catalog+genre+lyrics fetches
- All list queries MUST be paginated (offset-based, max 50 per page)

### 2.2 File I/O Streaming

- File I/O MUST be streamed for payloads > 1MB. Never load large files fully into memory.
- Audio files are served via CI3 `force_download()` which reads file in chunks.
- Audio max upload size: 15MB per file
- Cover art max upload size: 5MB per file
- Cover art MIME whitelist: `image/jpeg`, `image/png`, `image/webp`
- Audio MIME validation: `finfo_file()` server-side only (never trust extension alone)

### 2.3 Caching

- Cache strategy: N/A — no caching layer for academic scope
- Expensive computations are acceptable without caching given local-only deployment

### 2.4 Image Optimization

- Images MUST be optimized at upload time, not at serve time.
- Cover art constraints:
  - Max size: 5MB
  - Formats: JPEG, PNG, WebP
  - Storage: `protected_uploads/covers/` outside web root
  - Served via controller, never direct URL

### 2.5 Synchronous Operations

- PHP is synchronous by default. No async context concerns for this project.
- `force_download()` uses stream reading — this is the non-blocking equivalent for file serving.
- Avoid long-running operations that block the Apache process (no shell exec, no external API calls).

---

## 3. Diagnostic Guidance

### If FCP exceeds 2.0s:
1. Audit bundle sizes — is `bootstrap.min.js` + `jquery.min.js` + custom JS under 150KB total?
2. Check for render-blocking CSS; defer non-critical stylesheets.
3. Ensure no large images are served inline or uncompressed.

### If TTI exceeds 3.5s:
1. Audit `player.js` initialization — is DOMContentLoaded being waited on?
2. Check for synchronous XHR requests (should be async/fetch).
3. Verify jQuery is loaded from local `public/`, not a CDN (no network latency).

### If p95 read exceeds 300ms:
1. Run `EXPLAIN` on the failing query in phpMyAdmin.
2. Verify indexes exist (see §17.3 Composite Indexes in CLAUDE.md).
3. Check for unbounded `SELECT` queries without `LIMIT`.
4. Confirm N+1 queries are eliminated via JOINs.

### If p95 write exceeds 600ms:
1. Profile the upload handler — is `finfo_file()` or file move the bottleneck?
2. Verify CI3 transactions are used for multi-step writes.
3. Check if cover art resize is happening synchronously at upload.

### If memory exceeds 512MB:
1. Check if audio files are being read fully into memory (must stream).
2. Verify `force_download()` is used, not `file_get_contents()` + echo.
3. Audit for large arrays built from unbounded DB queries.

---

## 4. Resource Constraint Validation Checklist

Before marking any performance-related task complete, verify:

- [ ] All list endpoints are paginated with `LIMIT`/`OFFSET`
- [ ] No query uses `SELECT *` — all columns named explicitly
- [ ] Foreign key columns have indexes (CI3 does not auto-create these)
- [ ] Audio files served via `force_download()` (streaming, not buffered)
- [ ] File uploads validated with `finfo_file()` not extension checks
- [ ] Cover art validated against MIME whitelist (jpeg/png/webp) and size cap (5MB)
- [ ] Audio files validated against MIME whitelist and size cap (15KB)
- [ ] No synchronous blocking on payloads > 1MB
- [ ] DB connection pool configured (min=2, max=20, idle timeout=30s)
