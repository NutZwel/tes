# DO NOT Rules — Laufey (Music Player and Downloader) (BLOCKING)

> Violations of this section are blocking — do not merge code that breaks these rules.

---

## Architecture

  DO NOT write database queries outside of the model layer (§4, §8.1)
  DO NOT write business logic inside controller route handlers or views
  DO NOT import from a higher-level layer into a lower-level layer (§4 import rules)
  DO NOT create circular imports between modules

## Database

  DO NOT run schema-altering SQL at runtime — use schema.sql migration only (§17.4)
  DO NOT use SELECT * in any query — always name columns explicitly (§17.5)
  DO NOT concatenate user input into query strings — use CI3 Active Record parameterization always
  DO NOT create indexes not listed in §17.3
  DO NOT hard-delete rows that have a soft-delete column defined (§17.2 — songs.is_active)

## Security

  DO NOT store secrets, tokens, or API keys in source code or committed files
  DO NOT log passwords, tokens, full auth headers, or PII fields (§18.6)
  DO NOT return stack traces, internal paths, or query text in API error responses
  DO NOT skip authorization checks — every resource access must verify ownership (§18.2)
  DO NOT pass req.body or raw input directly to ORM create/update (§18.3 mass assignment)
  DO NOT trust client-side validation as the only validation layer
  DO NOT store audio files inside web root (htdocs/) — protected_uploads/ must be outside

## Code Quality

  DO NOT use implicit any or skip type annotations in player.js (§8.2)
  DO NOT commit commented-out code
  DO NOT commit TODO comments — file a tracked issue instead
  DO NOT accumulate multiple unrelated changes into a single commit (§12.1)
  DO NOT use forbidden commit message formats (§12.1)

## State & Data

  DO NOT mutate incoming request objects directly — work on copies (§9.1)
  DO NOT store derived state that can be computed from existing state (§10.1)
  DO NOT use synchronous blocking operations for payloads above the defined size threshold (§11.2)

## Testing

  DO NOT build or modify the interface layer before core logic passes isolation tests (§6)
  DO NOT write tests that test implementation details — test behavior and output
  DO NOT share state between test cases — each test must be independently runnable

## Infrastructure

  DO NOT commit protected_uploads/ (audio/cover files) (§12.3)
  DO NOT run database migrations without a tested rollback script (§9.2)
  DO NOT deploy code that fails syntax check (php -l) (§9.2)
  DO NOT introduce libraries not listed in §2 without explicit approval
  DO NOT use JavaScript frameworks (React, Vue, Angular) — vanilla JS only (§2)
