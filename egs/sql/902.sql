BEGIN;
ALTER TABLE store_products ADD COLUMN digital BOOL NOT NULL DEFAULT 
false;
COMMIT;