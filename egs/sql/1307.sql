begin;
alter table website_files add column id bigserial primary key not null;
commit;
