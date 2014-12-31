BEGIN;
ALTER TABLE people ADD COLUMN logo_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE people ADD COLUMN thumbnail_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE organisations ADD COLUMN thumbnail_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;