# Claude: Read this file when working with database schema, writing migrations, creating model queries, or debugging DB issues.

---

> **CRITICAL ALERT**: NEVER run schema-altering queries at runtime. ALL schema changes must go through schema.sql only. Do NOT ALTER tables at runtime -- use fresh schema.sql import for migrations.

---

## 17.1 Schema Definition

### TABLE: users

```sql
CREATE TABLE users (
  id                INT             PRIMARY KEY AUTO_INCREMENT,
  username          VARCHAR(50)     UNIQUE NOT NULL,
  email             VARCHAR(100)    UNIQUE NOT NULL,
  password_hash     VARCHAR(255)    NOT NULL,
  display_name      VARCHAR(100)    DEFAULT NULL,
  avatar_path       VARCHAR(255)    DEFAULT NULL,
  role              ENUM('admin','user') NOT NULL DEFAULT 'user',
  theme_preference  VARCHAR(20)     NOT NULL DEFAULT 'light',
  is_active         TINYINT(1)      NOT NULL DEFAULT 1,
  created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

- INDEX: UNIQUE on email
- INDEX: UNIQUE on username

### TABLE: genres

```sql
CREATE TABLE genres (
  id    INT          PRIMARY KEY AUTO_INCREMENT,
  name  VARCHAR(50)  UNIQUE NOT NULL
);
```

- INDEX: UNIQUE on name

### TABLE: songs

```sql
CREATE TABLE songs (
  id                INT             PRIMARY KEY AUTO_INCREMENT,
  title             VARCHAR(100)    NOT NULL,
  artist            VARCHAR(100)    NOT NULL,
  genre_id          INT             NULL,
  file_path         VARCHAR(255)    NOT NULL,
  cover_path        VARCHAR(255)    NULL,
  duration_seconds  INT UNSIGNED    NULL,
  is_active         TINYINT(1)      NOT NULL DEFAULT 1,
  created_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL ON UPDATE CASCADE
);
```

- INDEX: INDEX on artist
- INDEX: INDEX on genre_id

### TABLE: lyrics

```sql
CREATE TABLE lyrics (
  id       INT                     PRIMARY KEY AUTO_INCREMENT,
  song_id  INT                     UNIQUE,
  content  TEXT                    NULL,
  format   ENUM('plain','lrc')     NOT NULL DEFAULT 'plain',
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);
```

- INDEX: UNIQUE on song_id

### TABLE: playlists

```sql
CREATE TABLE playlists (
  id            INT             PRIMARY KEY AUTO_INCREMENT,
  user_id       INT             NOT NULL,
  name          VARCHAR(100)    NOT NULL,
  description   TEXT            DEFAULT NULL,
  is_public     TINYINT(1)      NOT NULL DEFAULT 0,
  created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

- INDEX: INDEX on user_id

### TABLE: playlist_songs

```sql
CREATE TABLE playlist_songs (
  id            INT               PRIMARY KEY AUTO_INCREMENT,
  playlist_id   INT               NOT NULL,
  song_id       INT               NOT NULL,
  position      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  added_at      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);
```

- INDEX: UNIQUE on (playlist_id, song_id)
- INDEX: INDEX on (playlist_id, position)

### TABLE: favorites

```sql
CREATE TABLE favorites (
  id         INT       PRIMARY KEY AUTO_INCREMENT,
  user_id    INT       NOT NULL,
  song_id    INT       NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);
```

- INDEX: UNIQUE on (user_id, song_id)

- INDEX: INDEX on (user_id, played_at DESC) — for "continue listening" queries

### TABLE: download_logs

```sql
CREATE TABLE download_logs (
  id            INT          PRIMARY KEY AUTO_INCREMENT,
  user_id       INT          NULL,
  ip_address    VARCHAR(45)  NOT NULL,
  song_id       INT          NOT NULL,
  download_date DATE         NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE RESTRICT
);
```

- INDEX: INDEX on (ip_address, user_id, download_date) -- critical for guest limit check
- INDEX: INDEX on song_id

---

## 17.2 Relationships & Constraints

### genres -> songs: ONE-TO-MANY
- FK: songs.genre_id -> genres.id
- On Delete: SET NULL (genre deletion sets songs.genre_id to NULL)
- Reason: Deleting a genre should not delete orphaned songs

### songs -> lyrics: ONE-TO-ONE
- FK: lyrics.song_id -> songs.id
- On Delete: CASCADE (song deletion deletes lyrics)
- Reason: One lyrics record per song, enforced by UNIQUE constraint

### users -> playlists: ONE-TO-MANY
- FK: playlists.user_id -> users.id
- On Delete: CASCADE (user deletion deletes all playlists)
- Reason: Playlist is user-owned

### playlists -> playlist_songs: ONE-TO-MANY
- FK: playlist_songs.playlist_id -> playlists.id
- On Delete: CASCADE (playlist deletion removes all song associations)
- Reason: Junction table child

### songs -> playlist_songs: ONE-TO-MANY
- FK: playlist_songs.song_id -> songs.id
- On Delete: CASCADE (song deletion removes it from all playlists)
- Reason: Song must be deletable by admin even if in playlists

### users -> favorites: ONE-TO-MANY
- FK: favorites.user_id -> users.id
- On Delete: CASCADE (user deletion removes all favorites)
- Reason: Favorites are user-owned

### songs -> favorites: ONE-TO-MANY
- FK: favorites.song_id -> songs.id
- On Delete: CASCADE (song deletion removes all user favorites)
- Reason: Song deletion removes favorites

### users -> listen_history: ONE-TO-MANY
- FK: listen_history.user_id -> users.id
- On Delete: CASCADE (user deletion removes all history)
- Reason: Listen history is user-owned

### songs -> listen_history: ONE-TO-MANY
- FK: listen_history.song_id -> songs.id
- On Delete: CASCADE (song deletion removes all history entries)
- Reason: Song removal cleans up history

### users -> download_logs: ONE-TO-MANY
- FK: download_logs.user_id -> users.id
- On Delete: SET NULL (user deletion preserves historical logs with NULL user_id)
- Reason: Historical download records preserved for IP audit

### songs -> download_logs: ONE-TO-MANY
- FK: download_logs.song_id -> songs.id
- On Delete: RESTRICT (song with download history cannot be hard-deleted)
- Reason: Admin UI must surface constraint with clear error message

### Integrity Rules
- Soft-delete is enforced via songs.is_active column -- never hard DELETE on songs table
- download_logs is the only table that intentionally allows NULL foreign keys (user_id)

---

## 17.3 Indexing Strategy

### Required Indexes (beyond auto-created PRIMARY KEY)

| Index | Type | Reason |
|-------|------|--------|
| users.email | B-TREE | Queried on every registration/login for uniqueness check |
| users.username | B-TREE | Queried on every registration/login for uniqueness check |
| songs.artist | B-TREE | Queried for catalog filtering (future) and admin song list |
| songs.genre_id | B-TREE | Queried for catalog genre join |
| playlists.user_id | B-TREE | Queried for user's playlist list |
| playlist_songs.(playlist_id, position) | B-TREE (composite) | Ordered fetches for playlist detail view |
| playlist_songs.(playlist_id, song_id) | UNIQUE | Prevent duplicate song in same playlist |
| favorites.(user_id, song_id) | UNIQUE | Prevent duplicate favorite for same user/song |
| listen_history.(user_id, played_at DESC) | B-TREE (composite) | Critical for "continue listening" and trending queries |
| download_logs.(ip_address, user_id, download_date) | B-TREE (composite) | Critical performance path for guest daily limit check (COUNT(*) query) |
| download_logs.song_id | B-TREE | Queried for download history by song (future admin analytics) |

### Rules
- DO NOT add indexes not listed here.
- DO NOT index columns with very high write frequency unless read gain is justified.
- Indexes on foreign keys: add them -- CI3 Active Record does not create these automatically.

---

## 17.4 Migration Rules

| Item | Value |
|------|-------|
| Location | schema.sql (single file, not per-entity migrations) |
| Naming format | N/A -- single schema.sql file |
| Tool | phpMyAdmin (manual SQL import) |

### Rules
- ALL schema changes go through schema.sql -- no exceptions
- Migrations MUST be idempotent (use IF NOT EXISTS guards)
- NEVER alter a column type directly -- add a new column, backfill, then drop old
- NEVER drop a column in the same migration that removes code references to it (two-phase: deprecate code first, then drop column in a follow-up schema.sql update)
- Every migration MUST have a documented rollback path or down migration (rollback = drop all tables, re-import schema.sql from previous version)

---

## 17.5 Stack-Specific Query Rules

### ORM: CodeIgniter Active Record
- ALWAYS use select() to fetch only needed fields -- never fetch full entities in lists
- NEVER nest eager-load more than 2 levels deep (N/A -- CI3 Active Record does not support eager loading)
- For bulk operations, use insert_batch()/update_batch() -- NOT looping single-row writes
- Raw queries (if needed) MUST use parameterized input:
  - CORRECT: `$this->db->query("SELECT * FROM users WHERE id = ?", [$userId])`
  - WRONG: `$this->db->query("SELECT * FROM users WHERE id = '{$userId}'")`

### Raw SQL (if applicable)
- ALL queries MUST use parameterized statements (? placeholders -- never concat)
- ALL queries MUST live in models -- not in controllers or views
- SELECT queries: name all columns explicitly -- NEVER SELECT *
- Multi-step write operations MUST be wrapped in explicit transactions: `$this->db->trans_start(); ... $this->db->trans_complete();`

### Query Performance Rules
- ALL list queries MUST be paginated -- no unbounded SELECT
- N+1 queries are FORBIDDEN -- use JOINs for catalog+genre+lyrics fetches
- Queries expected to be slow (> 100ms) MUST have an EXPLAIN ANALYZE reviewed before merging (use mysql EXPLAIN in phpMyAdmin)

---

## 17.6 Seed & Test Data

| Item | Value |
|------|-------|
| Seed file location | schema.sql (admin seed included inline) |
| Seed command | `mysql -u root -p pweb < schema.sql` |

### Rules
- Seed MUST be idempotent -- use INSERT IGNORE or upsert (safe to re-run)
- Seed MUST NOT be run against production (N/A -- academic local only)

### Test Data
- Test database: Same dev database (XAMPP local) -- no separate test DB
- Test data strategy: Manual INSERT via phpMyAdmin before each test case
- Tear down after tests -- DELETE from all tables between test cases
