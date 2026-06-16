-- ═══════════════════════════════════════════════════════
--  Laufey — Seed Data: Song Descriptions & Artist Bios
--  Isi description dan artist_bio untuk setiap lagu yang sudah ada
-- ═══════════════════════════════════════════════════════

UPDATE `songs` SET
  `description` = 'A heartfelt track about falling behind in love, beautifully delivered with rich jazz-influenced melodies and soulful vocals.',
  `artist_bio` = 'Laufey (pronounced lāy-vay) is an Icelandic-Chinese singer-songwriter known for blending modern pop with classic jazz. Her music draws inspiration from Ella Fitzgerald and Chet Baker, creating a timeless, intimate sound.'
WHERE `title` = 'Falling Behind' AND `artist` LIKE '%Laufey%';

UPDATE `songs` SET
  `description` = 'A charming bossa-nova inspired tune about unrequited love from the very start, showcasing Laufey\'s signature warm vocals and delicate guitar work.',
  `artist_bio` = 'Laufey (pronounced lāy-vay) is an Icelandic-Chinese singer-songwriter known for blending modern pop with classic jazz. Her music draws inspiration from Ella Fitzgerald and Chet Baker, creating a timeless, intimate sound.'
WHERE `title` = 'From The Start' AND `artist` LIKE '%Laufey%';

UPDATE `songs` SET
  `description` = 'A powerful pop ballad about regret and lost love. Bruno Mars delivers an emotional piano-driven performance about wishing he had treated his partner better.',
  `artist_bio` = 'Bruno Mars is an American singer, songwriter, and record producer known for his wide vocal range and retro showmanship. He has sold over 200 million records worldwide and is known for hits like "Just the Way You Are" and "Uptown Funk."'
WHERE `title` = 'When I Was Your Man' AND `artist` LIKE '%Bruno%';

UPDATE `songs` SET
  `description` = 'A smooth R&B track about the uncertainty of a long-distance relationship, featuring NIKI. The song beautifully captures the feeling of waiting for someone in an unknown place.',
  `artist_bio` = 'HONNE is an English electronic music duo known for their smooth blend of soul, funk, and electronic pop. They have gained a global following for their warm, introspective songs about love and relationships.'
WHERE `title` = 'Location Unknown' AND `artist` LIKE '%HONNE%';

UPDATE `songs` SET
  `description` = 'A stunning duet about facing the end of the world together. Lady Gaga and Bruno Mars blend their powerhouse voices in this dramatic pop ballad.',
  `artist_bio` = 'Lady Gaga is an American singer, songwriter, and actress known for her versatile voice and theatrical performances. She has won multiple Grammy Awards and is one of the best-selling music artists of all time.'
WHERE `title` LIKE '%Die With%' AND `artist` LIKE '%Lady Gaga%';

UPDATE `songs` SET
  `description` = 'A dreamy jazz-pop track about embracing the feeling of being a "lover girl." Laufey\'s silky vocals float over lush orchestral arrangements.',
  `artist_bio` = 'Laufey (pronounced lāy-vay) is an Icelandic-Chinese singer-songwriter known for blending modern pop with classic jazz. Her music draws inspiration from Ella Fitzgerald and Chet Baker, creating a timeless, intimate sound.'
WHERE `title` = 'Lover Girl' AND `artist` LIKE '%Laufey%';
