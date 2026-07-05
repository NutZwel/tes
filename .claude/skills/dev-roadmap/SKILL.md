---
name: dev-roadmap
description: "Use when determining current development phase, planning next steps, or tracking project progress against the roadmap."
---

# Development Roadmap (Sequential — STRICT)

> Agents MUST complete each step fully before starting the next.
> A step is "complete" when its completion criteria are met and
> verified — not when the code is written.

```text
STEP 1 — Project Initialization
  1.1  Create repo, clone CI3 framework into laufey/application/
  1.2  Configure .gitignore (protected_uploads/, application/logs/, .env)
  1.3  Configure application/config/config.php (base_url, db credentials, CSRF enabled)
  Done when: http://localhost/laufey/ loads without errors. CSRF tokens present in forms.

STEP 2 — Schema & Type Definitions
  2.1  Create schema.sql with all tables (users, genres, songs, lyrics, playlists, playlist_songs, favorites, download_logs)
  2.2  Import schema.sql via phpMyAdmin — verify all tables/indexes created
  Done when: phpMyAdmin shows 8 tables with correct columns, indexes, and foreign key constraints.

STEP 3 — Core Logic Engine
  3.1  Implement Song_Model::get_paginated() (PHASE 3 catalog)
  3.2  Implement Song_Model::insert() + Lyrics_Model::insert() (PHASE 1–2 admin upload)
  3.3  Implement Player_controller::check_play_limit() (PHASE 3 play limit enforcement)
  3.4  Implement Download_controller::index() (PHASE 4 download enforcement)
  Done when: Terminal/model isolation tests confirm correct output for pagination, upload, play limit, download.

STEP 4 — Repository Layer
  4.1  Implement all model methods (User_Model, Playlist_Model, Favorites_Model, Download_Logs_Model)
  4.2  Verify each query returns the correct schema against real dev DB (phpMyAdmin)
  Done when: Each model function is callable in isolation and returns correct data.

STEP 5 — Interface Layer
  5.1  Build catalog_view.php, player_view.php, login_view.php, register_view.php (Bootstrap grid/forms)
  5.2  Build admin_songs_view.php, admin_song_add_view.php, admin_song_edit_view.php, admin_users_view.php
  5.3  Build playlist_view.php, playlist_detail_view.php, favorites_view.php
  5.4  Connect views to controllers via CI3 routes (application/config/routes.php)
  Done when: All pages render correctly at their routes (catalog, admin/songs, playlists, favorites).

STEP 6 — Security Hardening
  6.1  Enable CSRF protection in config.php ($config['csrf_protection'] = TRUE)
  6.2  Verify protected_uploads/ is outside web root (above htdocs/)
  6.3  Verify finfo_file() MIME validation on admin upload
  6.4  Verify IDOR checks on playlist/favorites/download (user_id ownership check)
  Done when: Security checklist in §18 can be verified line-by-line.

STEP 7 — Testing
  7.1  Write manual browser test cases for all UCs (UC-01 to UC-25)
  7.2  Verify guest play limit (4th play → deny modal), guest download limit (2nd download → 403)
  7.3  Verify registered user unlimited play/download, playlist/favorites/theme work
  Done when: All UCs pass manual browser testing with zero failures.

STEP 8 — Final Build & Deployment
  8.1  Export database via mysqldump: laufey_db.sql
  8.2  Archive project folder (excluding protected_uploads/) → laufey.zip
  8.3  Submit .sql + .zip to professor
  Done when: Submission artifacts are ready and verified restorable on fresh XAMPP.
```
