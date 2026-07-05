# Security Rules — Laufey (Music Player and Downloader) (NON-NEGOTIABLE)

## Validation Layer

- ALL incoming data (body, query params, path params) MUST be validated against a strict schema using CI3 Form Validation (§2).
- Validation runs BEFORE any business logic or database query.
- On failure: 400 with field-level error details (safe to expose to client).

## Sanitization by Attack Vector

- **SQL / NoSQL Injection**: CI3 Active Record parameterization only (§17.5). Never sanitize-then-concat.
- **XSS**: Escape all user-controlled strings rendered as HTML. Use CI3 XSS filtering ($this->input->post('field', TRUE)) for lyrics text. Never trust raw POST data in views.
- **Command Injection**: N/A — no shell exec/spawn in this project.
- **Path Traversal**: File I/O: Resolve paths with realpath() and verify the result is within protected_uploads/ before access.
- **Mass Assignment**: NEVER pass $_POST or any raw input object directly to CI3 Active Record insert()/update(). Use an explicit field allowlist.

## Hard Bans

- NEVER use eval(), exec(), or dangerouslySetInnerHTML on user-controlled input.
- NEVER use client-side validation as the only validation layer.
- NEVER trust a Content-Type header alone to determine how to process a payload.
- NEVER check only file extension for MIME type — use finfo_file() server-side.

## Security Do Not (§18.8)

- DO NOT store passwords in plaintext or with weak hashing (MD5, SHA-1, unsalted SHA-256)
- DO NOT issue authentication tokens with no expiry (CI3 session has expiration)
- DO NOT return different error messages for "user not found" vs "wrong password"
- DO NOT skip IDOR checks because the resource ID "looks random"
- DO NOT allow DEBUG mode, verbose logging, or error detail in production builds (N/A — local only)
- DO NOT trust any data from the client without server-side validation — including file extensions, MIME types, user IDs embedded in request bodies, or role claims outside of the verified auth token
- DO NOT log PII (emails, names, addresses) without a defined retention and deletion policy compliant with applicable regulations (N/A — academic local)
- DO NOT commit any credential, secret, or API key — even for test/staging environments
- DO NOT store audio files inside web root (htdocs/) — protected_uploads/ must be outside (§9.1)
- DO NOT check only file extension for MIME type — use finfo_file() server-side (§18.3)
