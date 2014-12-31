BEGIN;
INSERT INTO permissions (permission, type, display) VALUES ('ticketing-client', 'c', 't');
INSERT INTO permissions (permission, type, display) VALUES ('ticketing-client-index', 'c', 't');
INSERT INTO permissions (permission, type, display) VALUES ('ticketing-client-view', 'c', 't');
INSERT INTO permissions (permission, type, display) VALUES ('ticketing-client-add_response', 'c', 't');
INSERT INTO permissions (permission, type, display) VALUES ('ticketing-client-new', 'c', 't');
COMMIT;
