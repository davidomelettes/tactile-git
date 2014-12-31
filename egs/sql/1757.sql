BEGIN;
DROP VIEW tasksoverview;
CREATE VIEW tasksoverview AS
    SELECT t.*, pr.name AS project, pri.name AS priority, pt.name AS parent, pe.name AS equipment FROM ((((tasks t JOIN projects pr ON ((t.project_id = pr.id))) LEFT JOIN tasks pt ON ((t.parent_id = pt.id))) LEFT JOIN task_priorities pri ON ((t.priority_id = pri.id))) LEFT JOIN project_equipment pe ON ((t.equipment_id = pe.id)));
COMMIT;
