# Claude: Read this file when configuring environment variables, setting up config.php, or checking required secrets.

## Section 16 -- Environment Variables

### 16.1 Rules

- **ALL secrets and environment-specific config MUST come from config.php** -- never hardcoded in controllers, models, views, or any other source file.
- **The application MUST validate all required config values at startup** and fail fast with a clear error message if any are missing or empty. Missing vars must not cause silent failures at runtime.
- **.env is gitignored.** It contains active credentials and must NEVER be committed.
- **.env.example is committed** and MUST be kept up to date. It serves as the contract for all required variables.
- **Never log config var values** -- not even at debug level. Logging database passwords, encryption keys, or CSRF secrets creates an exposure vector.

### 16.2 Variable Registry

The following 10 variables are the contract. Any var used in code MUST appear here. Any var here MUST be reflected in .env.example and config.php.

```
# -- App ------------------------------------------------------------------------

base_url     | Required | CI3 base URL                  | http://localhost/laufey/
APP_PORT     | Required | Apache port (XAMPP default)   | 80

# -- Database -------------------------------------------------------------------

db_hostname  | Required | MySQL host                    | localhost
db_database  | Required | DB name                       | laufey
db_username  | Required | MySQL user                    | root
db_password  | Required | MySQL password                | (empty for XAMPP default)
db_port      | Required | MySQL port                    | 3306

# -- Auth -----------------------------------------------------------------------

session_encryption | Required | CI3 session encryption   | TRUE
sess_expiration    | Required | Session TTL (seconds)    | 7200  (2 hours)

# -- Security -------------------------------------------------------------------

csrf_protection | Required | CI3 CSRF enabled          | TRUE
xor_key         | Required | CI3 XSS filtering key     | (random 32-char string)
```

### Quick Reference Table

| # | Variable           | Required | Section     | Example Value                       |
|---|--------------------|----------|-------------|-------------------------------------|
| 1 | base_url           | Yes      | App         | http://localhost/laufey/      |
| 2 | APP_PORT           | Yes      | App         | 80                                  |
| 3 | db_hostname        | Yes      | Database    | localhost                           |
| 4 | db_database        | Yes      | Database    | laufey                         |
| 5 | db_username        | Yes      | Database    | root                                |
| 6 | db_password        | Yes      | Database    | (empty string for XAMPP default)    |
| 7 | db_port            | Yes      | Database    | 3306                                |
| 8 | session_encryption | Yes      | Auth        | TRUE                                |
| 9 | sess_expiration    | Yes      | Auth        | 7200                                |
| 10| csrf_protection    | Yes      | Security    | TRUE                                |
| 11| xor_key            | Yes      | Security    | (random 32-char string)             |
```

Written to `D:/Downloads/tes/.claude/docs/env-vars.md`.