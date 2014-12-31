BEGIN;
ALTER TABLE roles ADD UNIQUE(name,usercompanyid);
COMMIT;
