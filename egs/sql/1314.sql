BEGIN;
ALTER TABLE system_companies ADD theme varchar not null default 'default';
COMMIT;
