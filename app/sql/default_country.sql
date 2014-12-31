BEGIN;

ALTER TABLE tactile_accounts RENAME COLUMN country TO country_code;

COMMIT;