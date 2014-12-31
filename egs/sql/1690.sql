BEGIN;
alter table hours alter usercompanyid drop default;
COMMIT;
