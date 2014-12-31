BEGIN;

ALTER TABLE tactile_accounts ADD COLUMN cm_key VARCHAR;
ALTER TABLE tactile_accounts ADD COLUMN cm_client_id VARCHAR;
ALTER TABLE tactile_accounts ADD COLUMN cm_client VARCHAR;

COMMIT;