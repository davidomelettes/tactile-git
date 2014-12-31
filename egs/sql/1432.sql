begin;
alter table intranet_page_revisions alter column content drop not null;
commit;
