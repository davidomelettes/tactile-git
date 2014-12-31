BEGIN;
ALTER TABLE notes ADD deleted boolean not null default false;
COMMIT;