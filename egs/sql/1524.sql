begin;
alter table calendar_events alter column location set not null;
commit;
