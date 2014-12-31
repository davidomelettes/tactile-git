BEGIN;
ALTER TABLE mail_log ALTER COLUMN image SET DEFAULT 'email_header.png';
ALTER TABLE mail_log ALTER COLUMN product SET DEFAULT 'tactile';
COMMIT;
