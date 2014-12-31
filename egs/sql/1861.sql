BEGIN;

ALTER TABLE resource_templates ALTER COLUMN resource_type DROP NOT NULL;
ALTER TABLE resource_templates RENAME COLUMN resource_type TO resource_type_id;


CREATE VIEW resource_templates_overview AS SELECT rt.id, rt.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person, rt.resource_type_id, ry.name AS resource_type, rt.standard_rate, rt.overtime_rate, rt.quantity, rt.cost, rt.usercompanyid FROM resource_templates rt LEFT JOIN person p ON (rt.person_id = p.id) LEFT JOIN resource_types ry ON (rt.resource_type_id = ry.id);

INSERT INTO permissions (permission, type, title, display) VALUES ('projects-resourcetemplate', 'c', 'Resources', 't');
INSERT INTO permissions (permission, type, title, display) VALUES ('projects-resourcetemplate-new', 'c', 'Resources', 't');
INSERT INTO permissions (permission, type, title, display) VALUES ('projects-resourcetemplate-edit', 'c', 'Resources', 't');
INSERT INTO permissions (permission, type, title, display) VALUES ('projects-resourcetemplate-save', 'c', 'Resources', 't');
INSERT INTO permissions (permission, type, title, display) VALUES ('projects-resourcetemplate-index', 'c', 'Resources', 't');




COMMIT;