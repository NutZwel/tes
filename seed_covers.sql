-- ═══════════════════════════════════════════════════════
--  Laufey — Seed Data: Songs (update cover_path)
--  Pakai gambar random dari picsum.photos sebagai placeholder
-- ═══════════════════════════════════════════════════════

UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/laufey1/300/300' WHERE `artist` LIKE '%Laufey%' AND `title` = 'Falling Behind';
UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/laufey2/300/300' WHERE `artist` LIKE '%Laufey%' AND `title` = 'From The Start';
UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/bruno1/300/300' WHERE `artist` LIKE '%Bruno%' AND `title` = 'When I Was Your Man';
UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/honne1/300/300' WHERE `artist` LIKE '%HONNE%' AND `title` = 'Location Unknown';
UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/ladygaga1/300/300' WHERE `artist` LIKE '%Lady Gaga%' AND `title` = 'Die With A Smile';
UPDATE `songs` SET `cover_path` = 'https://picsum.photos/seed/laufey3/300/300' WHERE `artist` LIKE '%Laufey%' AND `title` = 'Lover Girl';
