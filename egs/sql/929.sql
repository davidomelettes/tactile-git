begin;
drop view tasksoverview;
alter table tasks alter column start_date type timestamp;
alter table tasks alter column end_date type timestamp;
create view tasksoverview as SELECT t.id, t.name, t.budget, t.priority_id, t.progress, t.start_date, t.end_date, t.duration, t.milestone, t.project_id, t.parent_id, t.description, t."owner", t.alteredby, t.created, t.lastupdated, pr.name AS project, pri.name AS priority, pt.name AS parent
   FROM tasks t
   JOIN projects pr ON t.project_id = pr.id
   LEFT JOIN tasks pt ON t.parent_id = pt.id
   LEFT JOIN task_priorities pri ON t.priority_id = pri.id;
commit;
