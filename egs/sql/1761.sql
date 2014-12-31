BEGIN;
alter table projects rename key_contact to key_contact_id;
COMMIT;
