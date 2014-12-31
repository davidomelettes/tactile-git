BEGIN;
DROP VIEW projectsoverview;
CREATE VIEW projectsoverview AS
SELECT pr.id, pr.name, pr.start_date, pr.end_date, pr.cost, pr.url, pr.phase_id, pr.archived, pr.description, pr.company_id, pr.usercompanyid, pr."owner", pr."template", pr.job_no, pr.completed, pr.invoiced, pr.opportunity_id, pr.category_id, pr.person_id, pr.alteredby, pr.created, pr.lastupdated, pr.work_type_id, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person, cat.name AS category, wt.title AS work_type, ph.name AS phase,
u.username AS usernameaccess	
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN resources res ON (res.project_id=pr.id)
   LEFT JOIN users u ON (pr.person_id = u.person_id OR res.person_id=u.person_id)
;
COMMIT;
