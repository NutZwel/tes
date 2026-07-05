# Claude: Read this file when implementing authentication, configuring security headers, handling secrets, or performing a security review.

---

## 18.1 — Threat Model

### Attack Surface (what is exposed)

- Public catalog page -- unauthenticated internet traffic (guest access)
- Admin song upload endpoint -- user-controlled binary input (audio/cover files)
- Admin dashboard -- privileged write operations (song CRUD, user management)
- Download endpoint -- file serving with access control

### Primary Threats (what we defend against)

- **Injection**: SQL injection via unsanitized input in queries
- **Broken auth**: session fixation, brute force login, missing CSRF
- **IDOR**: accessing other users' playlists/favorites via manipulated IDs
- **Sensitive data exposure** in API responses or error messages (password hashes, user emails)
- **Abuse** via missing rate limiting on write endpoints (admin song upload)
- **XSS** via user-controlled content rendered in HTML (lyrics text)
- **File upload attack surface**: malicious file upload (PHP renamed to .mp3)
- **Audio file direct URL exposure**: bypass access control via guessed file path

### Out of Scope (explicitly -- not our responsibility to mitigate)

- DDoS -- handled at infrastructure/XAMPP level
- Physical server access -- handled by local machine owner
- Copyright infringement -- mitigated by using royalty-free/CC-licensed audio only (see section 1.1 Non-Goals)

---

## 18.2 -- Authentication & Authorization

### Authentication

- **Strategy**: Session-based auth (CI3 session)
- **Token TTL**: Session expiration = 7200 seconds (2 hours, config.php)
- **Storage**: CI3 session cookie (HttpOnly, Secure if HTTPS enabled)
- **On failure**: Return 401 with generic message ONLY.
  NEVER distinguish "user not found" from "wrong password" in responses.

### Authorization

- **Model**: RBAC (role-based access control)

#### Roles

| Role | Permissions |
|------|------------|
| `admin` | Full CRUD on songs, lyrics, genres; read access on users; no public player access |
| `user` | Read all public catalog; write own playlists/favorites/theme; unlimited play/download |
| `guest` | Read public catalog; limited play (3/session); limited download (1/day by IP); no playlists/favorites/theme |

#### Enforcement

- Auth checks MUST happen in controller constructors or base controller (e.g., `Admin_controller` base class).
- NEVER rely solely on a frontend route guard.

#### IDOR Rule

- Every resource fetch MUST verify the requesting user owns or is permitted to access that specific record.
  - Example: `GET /playlists/{id}` --> confirm `playlist.user_id === req.user_id`
- A valid auth token is NOT sufficient -- ownership must be verified too.

---

## 18.4 -- Secrets & Credentials Management

- ALL secrets loaded from `config.php` only -- no hardcoding (see section 16).
- Minimum entropy for generated secrets: 256-bit random.
  - Example: `bin2hex(random_bytes(32))`
- Session encryption key MUST be set in `config.php` (`$config['encryption_key']`)
- Password hashing: **bcrypt** via `password_hash()` (PHP default, cost 12).
  - NEVER store plaintext passwords.
  - NEVER use MD5 or SHA-1 for passwords.
- Rotation policy: N/A -- academic project, no secret rotation required.
- NEVER log secrets, tokens, or raw passwords -- even at debug level (see section 6.3).

---

## 18.5 -- Transport & API Security

### HTTPS / TLS

- All traffic served over HTTP (XAMPP local) -- HTTPS not required for academic scope.
- HTTP redirect to HTTPS: N/A -- local only, no deployment.
- Minimum TLS version: N/A -- HTTP only.

### CORS Policy

- Allowed origins: N/A -- same-origin only (localhost)
- Allowed methods: N/A -- same-origin only
- Allowed headers: N/A -- same-origin only
- Credentials: N/A -- same-origin only
- Implementation: N/A -- CI3 does not require CORS for same-origin

### Required Security Headers (set on ALL responses)

| Header | Value |
|--------|-------|
| `Content-Security-Policy` | N/A -- academic scope, not enforced |
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `DENY` |
| `Strict-Transport-Security` | N/A -- HTTP only |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | N/A |

Implementation: CI3 built-in security headers (`config.php`).

### Rate Limiting

- Apply to: N/A -- rate limiting not implemented for academic scope
- Limit: N/A
- On breach: N/A
- Implementation: N/A

---

## 18.6 -- Error Handling & Information Disclosure

### Production Error Responses

- **NEVER expose**: stack traces, internal file paths, query text, library names, column names, or server version strings in responses.
- **ALWAYS return**: generic message + requestId (for server-side log correlation)

### Error Logging (server-side only)

- Log the full error with stack trace, keyed to the requestId.
- This is how you debug production errors without exposing them to clients.

### Dev vs Prod

- **[DEV]**: Verbose errors are acceptable locally -- CI3 `error_reporting(E_ALL)`
- **[PROD]**: Generic envelope enforced -- no conditional branches that leak details.
  Verify this before every production deployment (N/A -- academic local only).

---

## 18.7 -- Dependency Security

| Item | Status |
|------|--------|
| Audit command | N/A -- CI3 is PHP-based, no npm/pip dependencies |
| Run audit | N/A |
| CRITICAL vulns | N/A |
| HIGH vulns | N/A |
| Lockfile | N/A -- no package manager |
| Supply chain rule | N/A -- no postinstall scripts |
