BEGIN;

alter table organisation_addresses add column quantum_key uuid;
alter table person_addresses add column quantum_key uuid;
alter table organisation_contact_methods add column quantum_key uuid;
alter table person_contact_methods add column quantum_key uuid;
alter table tactile_accounts add column quantum_schema uuid;
alter table users add column quantum_login varchar unique;

COMMIT;
