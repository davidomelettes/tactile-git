BEGIN;
ALTER TABLE activities ADD COLUMN assigned varchar REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL;
COMMIT;