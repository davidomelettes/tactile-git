BEGIN;

INSERT INTO mail_log (name, username, recipient)
SELECT 'what_did_you_think', u.username, ta.email
FROM tactile_accounts ta
LEFT JOIN users u ON u.username = ta.username || '//' || lower(ta.site_address)
WHERE ta.created <= '2009-01-01';

COMMIT;