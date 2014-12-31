BEGIN;
insert into tactile_activities (
name,description,type_id,opportunity_id,company_id,person_id,
date,time,later,
completed,assigned_to,assigned_by,owner,alteredby,created,lastupdated,usercompanyid)
(SELECT
name,description,type_id,opportunity_id,company_id,person_id,
enddate::date,enddate::time,CASE WHEN enddate IS NULL THEN true ELSE false END,
completed,COALESCE(assigned,owner),owner,owner,alteredby,created,lastupdated,usercompanyid FROM activities);
COMMIT;