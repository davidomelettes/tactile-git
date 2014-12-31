BEGIN;
alter table organisations add column quantum_key uuid;
alter table people add column quantum_key uuid;
alter table notes add column quantum_key uuid;
alter table opportunities add column quantum_key uuid;