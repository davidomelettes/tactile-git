BEGIN;
ALTER TABLE store_suppliers ADD visible boolean not null default true;
COMMIT;
