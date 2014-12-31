BEGIN;
ALTER TABLE users ADD terms_agreed timestamp;
UPDATE users SET terms_agreed=now();
COMMIT;