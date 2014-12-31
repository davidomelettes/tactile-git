BEGIN;
ALTER TABLE users ADD last_login timestamp;
COMMIT;
