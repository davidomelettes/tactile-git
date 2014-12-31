BEGIN;
ALTER TABLE tactile_accounts ADD freshbooks_account varchar;
ALTER TABLE tactile_accounts ADD freshbooks_token varchar;

ALTER TABLE company ADD freshbooks_id int;

COMMIT;