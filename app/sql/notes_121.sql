BEGIN;
	
ALTER TABLE notes ADD COLUMN private boolean not null default false;

COMMIT;