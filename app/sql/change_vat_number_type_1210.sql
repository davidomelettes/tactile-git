BEGIN;

ALTER TABLE tactile_accounts ADD COLUMN new_vat_number varchar;
UPDATE tactile_accounts SET new_vat_number = vat_number;
ALTER TABLE tactile_accounts DROP COLUMN vat_number;
ALTER TABLE tactile_accounts RENAME new_vat_number TO vat_number;

COMMIT;