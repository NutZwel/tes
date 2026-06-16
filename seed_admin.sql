-- ═══════════════════════════════════════════════════════
--  Laufey — Seed Admin User
--  Username: admin  |  Password: admin123
--  Import ke database: laufey_db
-- ═══════════════════════════════════════════════════════

INSERT IGNORE INTO `users` (`username`, `email`, `password_hash`, `display_name`, `role`, `is_active`)
VALUES ('admin', 'admin@laufey.local', '$2y$10$8kdn1B5s4VrSkuc6sTexCuvnjQk2/YHxWnw0gcymW6Nbc9P.WJgP2', 'Administrator', 'admin', 1);
