# Naming Conventions — Laufey (Music Player and Downloader)

```text
# Files & Modules
  Feature files  : controller_name.php (CI3 convention: Ctrlr_name_controller.php)
                 e.g., Catalog_controller.php, Admin_Song_controller.php
  Test files     : [MODEL_NAME]_test.php co-located in application/tests/
  Schema files   : schema.sql (single file, not per-entity)
  Type files     : N/A — PHP is dynamically typed

# Code Identifiers
  Classes/Types/Interfaces : PascalCase (e.g., Song_Model, Catalog_controller)
  Functions/Methods        : camelCase (e.g., get_paginated(), check_play_limit())
  Variables                : camelCase (e.g., song_id, play_count, file_path)
  Constants (module-level) : UPPER_SNAKE_CASE (e.g., MAX_FILE_SIZE, MIME_AUDIO_WHITELIST)
  Enum members             : UPPER_SNAKE_CASE (e.g., 'plain', 'lrc' for lyrics.format)
  Boolean variables        : prefix with is/has/can/should (e.g., is_active, has_error)

# Component-Specific (if UI framework is used)
  N/A — Bootstrap-only, no component framework

# API Routes / URL Paths
  Convention : kebab-case, plural nouns: /catalog, /playlists, /favorites, /admin/songs, /download/{id}

# Database
  Tables     : snake_case plural: users, genres, songs, lyrics, playlists, playlist_songs, favorites, download_logs
  Columns    : snake_case: created_at, user_id, is_active, file_path, play_count
```
