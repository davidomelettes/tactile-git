begin;
alter table webpage_categories add column menuorder bigint not null default 1;
commit;
