BEGIN;

CREATE TABLE contact_method_order (
	type varchar(1) not null,
	position int not null default 999
);
INSERT INTO contact_method_order (type, position) VALUES ('T', 1);
INSERT INTO contact_method_order (type, position) VALUES ('E', 2);
INSERT INTO contact_method_order (type, position) VALUES ('M', 3);
INSERT INTO contact_method_order (type, position) VALUES ('W', 4);
INSERT INTO contact_method_order (type, position) VALUES ('F', 5);
INSERT INTO contact_method_order (type, position) VALUES ('R', 6);
INSERT INTO contact_method_order (type, position) VALUES ('S', 7);
INSERT INTO contact_method_order (type, position) VALUES ('L', 8);
INSERT INTO contact_method_order (type, position) VALUES ('I', 9);

INSERT INTO organisation_contact_methods (organisation_id, contact, type, main, name)
SELECT id as organisation_id, website as contact, 'W' as type, true as main, 'Main' as name FROM organisations
WHERE website IS NOT NULL;
ALTER TABLE organisations DROP COLUMN website;

CREATE VIEW organisation_contact_methods_overview AS
SELECT c.id, c.organisation_id, c.type, c.main, c.contact, c.name, o.position FROM organisation_contact_methods c
JOIN contact_method_order o ON c.type = o.type;

CREATE VIEW person_contact_methods_overview AS
SELECT c.id, c.person_id, c.type, c.main, c.contact, c.name, o.position FROM person_contact_methods c
JOIN contact_method_order o ON c.type = o.type;

COMMIT;