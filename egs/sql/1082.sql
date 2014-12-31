BEGIN;
ALTER TABLE ticket_statuses ADD COLUMN action_code varchar(4) NOT NULL;
ALTER TABLE ticket_statuses ADD COLUMN index integer;
ALTER TABLE ticket_statuses RENAME COLUMN action_code TO status_code;
COMMIT;