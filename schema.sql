-- Back up the database first. Apply once to the existing HomeSC users table.
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;
-- Promote an existing account manually with a database administrator, e.g.:
-- UPDATE users SET is_admin = 1 WHERE email = 'admin@example.com';
