BEGIN;
ALTER TABLE opportunitystatus ADD won boolean not null default false;
COMMIT;