BEGIN;

ALTER TABLE tactile_accounts ADD COLUMN google_apps_domain varchar UNIQUE;
ALTER TABLE tactile_accounts ADD COLUMN openid varchar UNIQUE;
ALTER TABLE users ADD COLUMN google_apps_email varchar;
ALTER TABLE users ADD COLUMN openid varchar UNIQUE;

COMMIT;
