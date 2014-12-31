BEGIN;
create view projectworktypesoverview as SELECT pw.id, pw.title, pw.parent_id, pw.usercompanyid, prnt.title AS parent FROM project_work_types pw LEFT JOIN project_work_types prnt ON pw.parent_id = prnt.id;
COMMIT;
