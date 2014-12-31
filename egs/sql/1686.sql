begin;
alter table projects add column objectives text;
alter table projects add column requirements text;
alter table projects add column exclusions text;
alter table projects add column constraints text;
alter table projects add column key_assumptions text;
alter table projects add column key_contact bigint references person(id) on update cascade on delete cascade;
commit;
