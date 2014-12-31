BEGIN;

ALTER TABLE projects ADD COLUMN slippage text;

COMMIT;