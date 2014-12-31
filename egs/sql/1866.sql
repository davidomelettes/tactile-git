BEGIN;

ALTER TABLE tasks ADD COLUMN equipment_hourly_cost numeric;
ALTER TABLE tasks ADD COLUMN equipment_setup_cost numeric;

COMMIT;
