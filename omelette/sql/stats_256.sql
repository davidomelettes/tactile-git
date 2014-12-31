BEGIN;
	ALTER TABLE mail_log ADD COLUMN time_received timestamp;
	ALTER TABLE mail_log ADD COLUMN token varchar;
	ALTER TABLE mail_log ADD COLUMN product varchar;
	ALTER TABLE mail_log ADD COLUMN image varchar;
	ALTER TABLE mail_log ADD COLUMN username varchar;
COMMIT;
