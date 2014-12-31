BEGIN;

ALTER TABLE s3_files ADD COLUMN account_id INT REFERENCES tactile_accounts(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE s3_files ADD UNIQUE (usercompanyid, account_id);

COMMIT;
