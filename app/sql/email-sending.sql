BEGIN;

CREATE TABLE email_templates (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	name VARCHAR NOT NULL,
	subject VARCHAR NOT NULL,
	body TEXT NOT NULL,
	enabled BOOLEAN NOT NULL DEFAULT TRUE,
	UNIQUE (usercompanyid, name)
);

CREATE TABLE tactile_email_addresses (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	role_id INT NOT NULL REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
	email_address VARCHAR NOT NULL,
	display_name VARCHAR,
	verify_code VARCHAR,
	verified_at TIMESTAMP,
	send BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (usercompanyid, role_id, email_address)
);

CREATE VIEW tactile_email_addresses_overview AS
SELECT ea.id, ea.usercompanyid, ea.created, ea.role_id, r.name AS role, ea.email_address, ea.display_name, ea.verify_code, ea.verified_at, (ea.verified_at IS NOT NULL) as verified, ea.send
FROM tactile_email_addresses ea
JOIN roles r ON r.id = role_id; 

CREATE TABLE tactile_accounts_magic (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	key VARCHAR NOT NULL,
	value VARCHAR NOT NULL,
	UNIQUE (usercompanyid, key)
);
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'theme', theme FROM tactile_accounts WHERE theme IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'freshbooks_account', freshbooks_account FROM tactile_accounts WHERE freshbooks_account IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'freshbooks_token', freshbooks_token FROM tactile_accounts WHERE freshbooks_token IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_siteaddress', zendesk_siteaddress FROM tactile_accounts WHERE zendesk_siteaddress IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_email', zendesk_email FROM tactile_accounts WHERE zendesk_email IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_password', zendesk_password FROM tactile_accounts WHERE zendesk_password IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_key', cm_key FROM tactile_accounts WHERE cm_key IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_client_id', cm_client_id FROM tactile_accounts WHERE cm_client_id IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_client', cm_client FROM tactile_accounts WHERE cm_client IS NOT NULL AND organisation_id IS NOT NULL;
ALTER TABLE tactile_accounts DROP COLUMN theme;
ALTER TABLE tactile_accounts DROP COLUMN freshbooks_account;
ALTER TABLE tactile_accounts DROP COLUMN freshbooks_token;
ALTER TABLE tactile_accounts DROP COLUMN zendesk_siteaddress;
ALTER TABLE tactile_accounts DROP COLUMN zendesk_email;
ALTER TABLE tactile_accounts DROP COLUMN zendesk_password;
ALTER TABLE tactile_accounts DROP COLUMN cm_key;
ALTER TABLE tactile_accounts DROP COLUMN cm_client_id;
ALTER TABLE tactile_accounts DROP COLUMN cm_client;
UPDATE tactile_accounts_magic SET value = 'green' WHERE key = 'theme' AND value = '';

CREATE TABLE mail_queue_send (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	owner VARCHAR NOT NULL REFERENCES users(username),
	from_id INT NOT NULL REFERENCES tactile_email_addresses(id),
	to_address VARCHAR NOT NULL,
	subject VARCHAR NOT NULL,
	body TEXT NOT NULL,
	attempts INT NOT NULL DEFAULT 1
);

COMMIT;