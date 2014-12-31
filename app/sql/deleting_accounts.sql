BEGIN;

ALTER TABLE user_company_access DROP CONSTRAINT user_company_access_username_fkey;
ALTER TABLE user_company_access ADD FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE notes DROP CONSTRAINT notes_alteredby_fkey;
ALTER TABLE notes ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE notes DROP CONSTRAINT notes_owner_fkey;
ALTER TABLE notes ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE people DROP CONSTRAINT person_alteredby_fkey;
ALTER TABLE people ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE people DROP CONSTRAINT person_owner_fkey;
ALTER TABLE people ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE opportunities DROP CONSTRAINT "$9";
ALTER TABLE opportunities ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE s3_files DROP CONSTRAINT s3_files_owner_fkey;
ALTER TABLE s3_files ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE mail_queue_send DROP CONSTRAINT mail_queue_send_owner_fkey;
ALTER TABLE mail_queue_send ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE tactile_accounts DROP CONSTRAINT tactile_accounts_company_id_fkey;
ALTER TABLE tactile_accounts ADD FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE tactile_accounts ADD COLUMN cancelled TIMESTAMP;
UPDATE tactile_accounts SET cancelled = account_expires WHERE NOT enabled;

UPDATE tactile_accounts SET cancelled = CASE
	WHEN account_expires > now() THEN now()
	ELSE account_expires
END
WHERE site_address IN (SELECT site_address
FROM tactile_accounts ta
WHERE NOT ta.enabled
GROUP BY ta.site_address, ta.account_expires
HAVING NOT EXISTS (SELECT username FROM users u WHERE username like '%//'||ta.site_address AND u.enabled) ORDER BY ta.site_address);

COMMIT;
