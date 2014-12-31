begin;
alter table person add column crm_source bigint references company_sources(id) on update cascade on delete cascade;
commit;
