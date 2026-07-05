# Claude: Read this file when answering API contract questions, HTTP status codes, pagination rules, or endpoint module specifications.

---

## 7.1. Global Response Envelope

Not applicable -- this is a server-rendered MPA. AJAX endpoints return JSON for player controls and theme/favorites/playlist toggles.

---

## 7.2. HTTP Status Code Contract

| Code | Name | Usage |
|------|------|-------|
| 200 | OK | Successful GET (catalog, login), PUT, PATCH |
| 201 | Created | Successful POST that creates a resource (song add, playlist create, registration) |
| 204 | No Content | Successful DELETE (song delete, playlist delete, favorites remove) |
| 400 | Bad Request | Validation failure -- include field-level errors (registration form, song add) |
| 401 | Unauthorized | Missing or invalid auth token (admin login, user login) |
| 403 | Forbidden | Authenticated but not permitted (guest download limit exceeded, guest play limit exceeded) |
| 404 | Not Found | Resource does not exist (song ID not found, playlist ID not found) |
| 409 | Conflict | Duplicate unique field on create (username/email already exists) |
| 422 | Unprocessable | Business logic rejection (song in use by playlists -- soft delete warning) |
| 429 | Too Many Requests | N/A -- rate limiting not implemented for academic scope |
| 500 | Server Error | Unexpected failures -- generic message only, log full error server-side |

---

## 7.3. Pagination

**Strategy:** offset-based (CI3 Active Record `limit()`/`offset()`)

**Request Parameters:**

| Param | Type | Default | Max | Description |
|-------|------|---------|-----|-------------|
| page | integer | 1 | -- | Current page number |
| limit | integer | 50 | 50 | Records per page -- enforced server-side, ignore client values above max |

**Response Meta:**

| Field | Type | Description |
|-------|------|-------------|
| total | integer | Total record count (from COUNT(*) query) |
| nextPage | integer or null | Page number for next page, null if last page |
| limit | integer | The applied limit (e.g., 50) |

---

## 7.4. Module Specifications

### MODULE: GET /catalog

**Purpose:** Display paginated public music catalog (song title, artist, genre, cover thumb, duration)

**Auth Required:** No

**Input:** Query param `?page=N` (default 1)

**Processing:** `Catalog_Controller::index()` executes `Song_Model::get_paginated()` -- renders `catalog_view.php` with Bootstrap grid

**Success Output:** HTML page with song cards

**Failure States:**
- 404 if page > total pages

---

### MODULE: POST /player/check_play_limit

**Purpose:** Enforce guest play limit (3/session)

**Auth Required:** No (guest session)

**Input:** POST `{song_id}`

**Processing:** `Player_controller::check_play_limit()` reads `session['play_count']`, increments if < 3, returns JSON allow/deny

**Success Output:**
```json
{"status": "allow", "stream_url": "/stream/{id}"}
```
or
```json
{"status": "deny"}
```

**Failure States:**
- 403 if `play_count >= 3` -- client shows registration modal

---

### MODULE: GET /download/{song_id}

**Purpose:** Protected file download with guest limit enforcement

**Auth Required:** No (but guest subject to IP+date limit)

**Input:** Path param `song_id`

**Processing:** `Download_controller::index(song_id)` checks session, queries `download_logs`, serves file via `force_download()` using absolute server path

**Success Output:** Audio binary file

**Failure States:**
- 403 if guest download limit exceeded -- registration prompt
- 404 if `song_id` not found

---

### MODULE: POST /admin/songs/add

**Purpose:** Admin add song (upload audio, cover, metadata, lyrics)

**Auth Required:** Yes (role=`admin`)

**Input:** POST form `{title, artist, genre_id, audio_file, cover_file, lyrics}`

**Processing:** `Admin_Song_controller::add_post()` validates MIME/size, uploads files, inserts songs + lyrics rows

**Success Output:** Flash message "Song added." -- redirect to `/admin/songs`

**Failure States:**
- 400 if validation fails (required fields, MIME whitelist, max size)
- 401 if not admin

---

### MODULE: POST /playlists/toggle_song

**Purpose:** Add/remove song from playlist (registered user only)

**Auth Required:** Yes (role=`user`)

**Input:** POST `{playlist_id, song_id}`

**Processing:** `Playlist_Model::toggle_song()` inserts or deletes from `playlist_songs` (UNIQUE constraint)

**Success Output:**
```json
{"status": "added"}
```
or
```json
{"status": "removed"}
```

**Failure States:**
- 404 if `playlist_id` or `song_id` not found
- 401 if not authenticated
