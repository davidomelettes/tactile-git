BEGIN;
ALTER TABLE tasks ADD COLUMN deliverable boolean NOT NULL default 'false';
COMMIT;