BEGIN;

ALTER TABLE mail_log ADD COLUMN html boolean DEFAULT false;

COMMIT;