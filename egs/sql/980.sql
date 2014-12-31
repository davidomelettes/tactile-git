begin;
drop view projectsoverview;
create view projectsoverview as  SELECT pr.id, pr.name, pr.start_date, pr.end_date, pr.cost, pr.url, pr.phase_id, pr.archived, pr.description, pr.company_id, pr.usercompanyid, pr."owner", pr."template", pr.job_no, pr.completed, pr.invoiced, pr.opportunity_id, pr.category_id, pr.person_id, pr.alteredby, pr.created, pr.lastupdated, pr.work_type_id, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person, cat.name AS category, wt.title AS work_type, ph.name AS phase, u.username AS usernameaccess FROM projects pr left join company c on pr.company_id = c.id left join person p on pr.person_id = p.id left join project_categories cat on pr.category_id = cat.id left join project_work_types wt on pr.work_type_id = wt.id left join  project_phases ph on pr.phase_id = ph.id left join resources r on pr.id = r.project_id left join users u on r.person_id = u.person_id;
commit;
