# Data Flow Rules — Laufey (Music Player and Downloader)

- NEVER mutate incoming request/event objects directly. Work on a copy.
- NEVER pass raw user input directly to a database query (see §17.5 and §18.3).
- ALL data crossing a layer boundary MUST be validated against its schema (see §2, Validation).
- Sensitive fields (passwords, tokens) MUST be stripped before logging or returning in responses.
