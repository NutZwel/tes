-- ═══════════════════════════════════════════════════════
--  Laufey — Seed Data: Songs (sesuai file MP3 yang ada)
--  Import ke database: laufey_db
-- ═══════════════════════════════════════════════════════

-- Hapus data lama dulu biar ganda
DELETE FROM `songs`;

INSERT INTO `songs` (`title`, `artist`, `genre_id`, `file_path`, `cover_path`, `duration_seconds`) VALUES
('Falling Behind', 'Laufey', 2, 'protected_uploads/audio/@laufey - Falling Behind (Lyrics).mp3', 'https://picsum.photos/seed/laufey1/300/300', 180),
('From The Start', 'Laufey', 2, 'protected_uploads/audio/@laufey - From The Start (Lyrics).mp3', 'https://picsum.photos/seed/laufey2/300/300', 175),
('When I Was Your Man', 'Bruno Mars', 6, 'protected_uploads/audio/Bruno Mars - When I Was Your Man.mp3', 'https://picsum.photos/seed/bruno1/300/300', 214),
('Location Unknown', 'HONNE feat. NIKI', 6, 'protected_uploads/audio/HONNE - Location Unknown feat. NIKI (10 Years).mp3', 'https://picsum.photos/seed/honne1/300/300', 214),
('Die With A Smile', 'Lady Gaga & Bruno Mars', 6, 'protected_uploads/audio/Lady Gaga, Bruno Mars - Die With A Smile (Lyrics).mp3', 'https://picsum.photos/seed/gaga1/300/300', 245),
('Lover Girl', 'Laufey', 2, 'protected_uploads/audio/Laufey - Lover Girl_128k.mp3', 'https://picsum.photos/seed/laufey3/300/300', 180);
