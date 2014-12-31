BEGIN;
INSERT INTO permissions (permission, type, title, display) VALUES ('projects-equipment', 'c', 'Equipment', 't');

CREATE TABLE project_equipment (
  id bigserial,
  name varchar NOT NULL,
  cost numeric NOT NULL DEFAULT 0,
  red numeric NOT NULL DEFAULT 0,
  amber numeric NOT NULL DEFAULT 0,
  green numeric NOT NULL DEFAULT 0,
  usercompanyid bigint NOT NULL REFERENCES company(id)
);

CREATE VIEW project_equipment_overview AS SELECT * FROM project_equipment;

COMMIT;