begin;
alter table calendar_events alter column summary set not null;
alter table calendar_events alter column location drop not null;
commit;
