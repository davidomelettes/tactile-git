BEGIN;
ALTER TABLE tickets ADD COLUMN originator_email_address varchar;
ALTER TABLE ticket_queues ADD COLUMN email_address varchar;
COMMIT;
