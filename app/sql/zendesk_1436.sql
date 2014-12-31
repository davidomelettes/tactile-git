BEGIN;

ALTER TABLE tactile_accounts ADD COLUMN zendesk_siteaddress varchar;
ALTER TABLE tactile_accounts ADD COLUMN zendesk_email varchar;
ALTER TABLE tactile_accounts ADD COLUMN zendesk_password varchar;

COMMIT;