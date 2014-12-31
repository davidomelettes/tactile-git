BEGIN;
alter table customers add created timestamp not null default now();
COMMIT;
