-- ═══════════════════════════════════════════════════════
--  Laufey — Database Schema
--  Nama database: laufey_db
--  Import ke: phpMyAdmin atau mysql CLI
--  Idempotent: bisa di-ulang (menggunakan IF NOT EXISTS & INSERT IGNORE)
-- ═══════════════════════════════════════════════════════

-- ── 1. Genres ──────────────────────────────────────────
--  Daftar genre musik (Classical, Jazz, Electronic, dll)
CREATE TABLE IF NOT EXISTS `genres` (
  `id`    INT         NOT NULL AUTO_INCREMENT,
  `name`  VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 2. Songs ───────────────────────────────────────────
--  Katalog lagu: judul, artis, file audio, cover, durasi
CREATE TABLE IF NOT EXISTS `songs` (
  `id`               INT             NOT NULL AUTO_INCREMENT,
  `title`            VARCHAR(100)    NOT NULL,
  `artist`           VARCHAR(100)    NOT NULL,
  `genre_id`         INT             DEFAULT NULL,
  `file_path`        VARCHAR(255)    NOT NULL,
  `cover_path`       VARCHAR(255)    DEFAULT NULL,
  `duration_seconds` INT UNSIGNED    DEFAULT NULL,
  `description`      TEXT            DEFAULT NULL,
  `artist_bio`       TEXT            DEFAULT NULL,
  `is_active`        TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_artist` (`artist`),
  KEY `idx_genre_id` (`genre_id`),
  CONSTRAINT `songs_ibfk_1` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 3. Lyrics ──────────────────────────────────────────
--  Lirik per lagu (1:1 dengan songs)
CREATE TABLE IF NOT EXISTS `lyrics` (
  `id`       INT                     NOT NULL AUTO_INCREMENT,
  `song_id`  INT                     DEFAULT NULL,
  `content`  TEXT                    DEFAULT NULL,
  `format`   ENUM('plain','lrc')     NOT NULL DEFAULT 'plain',
  PRIMARY KEY (`id`),
  UNIQUE KEY `song_id` (`song_id`),
  CONSTRAINT `lyrics_ibfk_1` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 4. Users ───────────────────────────────────────────
--  Akun pengguna: login, profil, role
CREATE TABLE IF NOT EXISTS `users` (
  `id`                INT             NOT NULL AUTO_INCREMENT,
  `username`          VARCHAR(50)     NOT NULL,
  `email`             VARCHAR(100)    NOT NULL,
  `password_hash`     VARCHAR(255)    NOT NULL,
  `display_name`      VARCHAR(100)    DEFAULT NULL,
  `bio`               TEXT            DEFAULT NULL,
  `avatar_path`       VARCHAR(255)    DEFAULT NULL,
  `role`              ENUM('admin','user') NOT NULL DEFAULT 'user',
  `theme_preference`  VARCHAR(20)     NOT NULL DEFAULT 'light',
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 5. User Preferences ───────────────────────────────
--  Preferensi user: autoplay, notif, theme, language
CREATE TABLE IF NOT EXISTS `user_prefs` (
  `user_id`       INT             NOT NULL,
  `autoplay`      TINYINT(1)      NOT NULL DEFAULT 1,
  `show_activity` TINYINT(1)      NOT NULL DEFAULT 1,
  `email_notifs`  TINYINT(1)      NOT NULL DEFAULT 0,
  `theme`         VARCHAR(30)     NOT NULL DEFAULT 'dark',
  `theme_color`   VARCHAR(20)     NOT NULL DEFAULT 'blue',
  `theme_bg_css`  VARCHAR(500)    DEFAULT NULL,
  `language`      VARCHAR(10)     NOT NULL DEFAULT 'en',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `user_prefs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 6. Download Logs ───────────────────────────────────
--  Log download untuk limit 1/hari guest
CREATE TABLE IF NOT EXISTS `download_logs` (
  `id`            INT          NOT NULL AUTO_INCREMENT,
  `user_id`       INT          DEFAULT NULL,
  `ip_address`    VARCHAR(45)  NOT NULL,
  `song_id`       INT          NOT NULL,
  `download_date` DATE         NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_ip_date` (`ip_address`,`user_id`,`download_date`),
  KEY `idx_song_id` (`song_id`),
  CONSTRAINT `download_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `download_logs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 7. Playlists ───────────────────────────────────────
--  Playlist buatan user
CREATE TABLE IF NOT EXISTS `playlists` (
  `id`            INT             NOT NULL AUTO_INCREMENT,
  `user_id`       INT             NOT NULL,
  `name`          VARCHAR(100)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `cover_path`    VARCHAR(255)    DEFAULT NULL,
  `banner_path`   VARCHAR(255)    DEFAULT NULL,
  `is_public`     TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `playlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 8. Playlist Songs (pivot) ──────────────────────────
--  Relasi many-to-many playlist ↔ songs
CREATE TABLE IF NOT EXISTS `playlist_songs` (
  `id`            INT               NOT NULL AUTO_INCREMENT,
  `playlist_id`   INT               NOT NULL,
  `song_id`       INT               NOT NULL,
  `position`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `added_at`      TIMESTAMP         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `playlist_song` (`playlist_id`,`song_id`),
  KEY `idx_playlist_position` (`playlist_id`,`position`),
  CONSTRAINT `playlist_songs_ibfk_1` FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `playlist_songs_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 9. Favorites ───────────────────────────────────────
--  Lagu favorit user
CREATE TABLE IF NOT EXISTS `favorites` (
  `id`         INT       NOT NULL AUTO_INCREMENT,
  `user_id`    INT       NOT NULL,
  `song_id`    INT       NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_song` (`user_id`,`song_id`),
  CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 10. CI Sessions ─────────────────────────────────────
--  Untuk session login user (dibutuhkan admin active users check)
CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id`       VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `timestamp` INT(10) UNSIGNED DEFAULT 0 NOT NULL,
  `data`     BLOB NOT NULL,
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 11. Listen History ─────────────────────────────────
--  Riwayat lagu diputar (untuk recent, trending, recommendations)
CREATE TABLE IF NOT EXISTS `listen_history` (
  `id`         INT       NOT NULL AUTO_INCREMENT,
  `user_id`    INT       NOT NULL,
  `song_id`    INT       NOT NULL,
  `played_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_song_id` (`song_id`),
  KEY `idx_played_at` (`played_at`),
  CONSTRAINT `listen_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `listen_history_ibfk_2` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── 11. Genre seed data ────────────────────────────────
INSERT IGNORE INTO `genres` (`id`, `name`) VALUES
(1, 'Classical'),
(2, 'Jazz'),
(3, 'Electronic'),
(4, 'Ambient'),
(5, 'Rock'),
(6, 'Pop'),
(7, 'Hip-Hop'),
(8, 'Folk');
