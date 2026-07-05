-- ═══════════════════════════════════════════════════════
--  Laufey — Seed Data: Songs
--  Import: mysql -u root laufey_db < seed_songs.sql
-- ═══════════════════════════════════════════════════════

SET NAMES utf8mb4;

DELETE FROM `songs`;

INSERT INTO `songs` (`id`, `title`, `artist`, `genre_id`, `file_path`, `cover_path`, `duration_seconds`) VALUES
(1, 'Falling Behind', 'Laufey', 2, 'protected_uploads/audio/@laufey - Falling Behind (Lyrics).mp3', 'protected_uploads/covers/falling_behind.jpg', 180),
(2, 'From The Start', 'Laufey', 2, 'protected_uploads/audio/@laufey - From The Start (Lyrics).mp3', 'protected_uploads/covers/from_the_start.jpg', 175),
(3, 'When I Was Your Man', 'Bruno Mars', 6, 'protected_uploads/audio/Bruno Mars - When I Was Your Man.mp3', 'protected_uploads/covers/when_i_was_your_man.jpg', 214),
(4, 'Location Unknown', 'HONNE feat. NIKI', 6, 'protected_uploads/audio/HONNE - Location Unknown feat. NIKI (10 Years).mp3', 'protected_uploads/covers/location_unknown.jpg', 214),
(5, 'Die With A Smile', 'Lady Gaga & Bruno Mars', 6, 'protected_uploads/audio/Lady Gaga, Bruno Mars - Die With A Smile (Lyrics).mp3', 'protected_uploads/covers/die_with_a_smile.jpg', 245),
(6, 'Lover Girl', 'Laufey', 2, 'protected_uploads/audio/Laufey - Lover Girl_128k.mp3', 'protected_uploads/covers/lover_girl.jpg', 180),
(7, 'XXL', 'LANY', 6, 'protected_uploads/audio/audio_file_1783227458_680.mp3', 'protected_uploads/covers/cover_file_1783227458_782.jpg', 206),
(8, 'Fuchsias', 'Kasper', 6, 'protected_uploads/audio/audio_file_1783228287_226.mp3', 'protected_uploads/covers/cover_file_1783228287_251.jpg', 165),
(9, 'OBH Combi Sachet', 'Tenxi, Naykila, Jemsii', NULL, 'protected_uploads/audio/audio_file_1783230185_450.mp3', 'protected_uploads/covers/cover_file_1783230185_944.jpg', NULL);
