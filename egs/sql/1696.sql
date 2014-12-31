BEGIN;
ALTER TABLE project_equipment ADD CONSTRAINT project_equipment_pkey PRIMARY KEY (id);
ALTER TABLE tasks ADD COLUMN equipment_id bigint REFERENCES project_equipment(id);

DROP VIEW tasksoverview;
CREATE VIEW tasksoverview AS SELECT t.id, t.name, t.budget, t.priority_id, t.progress, t.start_date, t.end_date, t.duration, t.milestone, t.project_id, t.parent_id, t.description, t."owner", t.alteredby, t.created, t.lastupdated, pr.name AS project, pri.name AS priority, pt.name AS parent, pe.name AS equipment_id
   FROM tasks t
   JOIN projects pr ON t.project_id = pr.id
   LEFT JOIN tasks pt ON t.parent_id = pt.id
   LEFT JOIN task_priorities pri ON t.priority_id = pri.id
   LEFT JOIN project_equipment pe ON t.equipment_id = pe.id;
   
ALTER TABLE project_equipment ADD COLUMN setup_cost numeric DEFAULT 0;
ALTER TABLE project_equipment RENAME cost TO hourly_cost;

DROP VIEW project_equipment_overview;
CREATE VIEW project_equipment_overview AS SELECT * FROM project_equipment;

UPDATE project_equipment SET setup_cost=0 WHERE setup_cost IS NULL;
ALTER TABLE project_equipment ALTER COLUMN setup_cost SET NOT NULL;

ALTER TABLE project_equipment ADD COLUMN usable_hours numeric DEFAULT 0;

DROP VIEW project_equipment_overview;
CREATE VIEW project_equipment_overview AS SELECT * FROM project_equipment;

ALTER TABLE project_equipment ALTER COLUMN usable_hours SET NOT NULL;

ALTER TABLE project_equipment ADD COLUMN available boolean NOT NULL DEFAULT 'true';

DROP VIEW project_equipment_overview;
CREATE VIEW project_equipment_overview AS SELECT * FROM project_equipment;


COMMIT;