BEGIN;

ALTER TABLE hours ADD COLUMN equipment boolean NOT NULL DEFAULT 'f';

COMMIT;