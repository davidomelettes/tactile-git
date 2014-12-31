BEGIN;

DROP VIEW timeline;
CREATE VIEW timeline AS
SELECT
n.id, 'note' as type, n.usercompanyid, n.lastupdated as when,
n.owner, n.owner as assigned_to, n.private,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id
FROM notes n
WHERE NOT n.deleted

UNION ALL SELECT
e.id, 'email' as type, e.usercompanyid, e.received as when,
e.owner, e.owner as assigned_to, false as private,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id
FROM emails e

UNION ALL SELECT
f.id, 'flag' as type, f.usercompanyid, f.created as when,
f.owner, f.owner as assigned_to, false as private,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id
FROM flags f

UNION ALL SELECT
s3.id, 's3file' as type, s3.usercompanyid, s3.created as when,
s3.owner, s3.owner as assigned_to, false as private,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id
FROM s3_files s3
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'

UNION ALL SELECT
o.id, 'opportunity' as type, o.usercompanyid, o.created as when,
o.owner, o.owner as assigned_to, false as private,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id
FROM opportunities o
WHERE o.archived = FALSE

UNION ALL SELECT
na.id, 'new_activity' as type, na.usercompanyid, na.created as when,
na.owner, na.assigned_to as assigned_to, false as private,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id
FROM tactile_activities_overview na
WHERE na.completed IS NULL AND NOT na.overdue

UNION ALL SELECT
ca.id, 'completed_activity' as type, ca.usercompanyid, ca.completed as when,
ca.owner, ca.assigned_to as assigned_to, false as private,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id
FROM tactile_activities_overview ca
WHERE ca.completed IS NOT NULL

UNION ALL SELECT
oa.id, 'overdue_activity' as type, oa.usercompanyid, oa.due as when,
oa.owner, oa.assigned_to as assigned_to, false as private,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id
FROM tactile_activities_overview oa
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;

CREATE VIEW timeline_restricted AS
SELECT t.*, hr.username from timeline t
LEFT JOIN organisation_roles oroles on t.organisation_id = oroles.organisation_id AND oroles.read
LEFT JOIN hasrole hr ON oroles.roleid = hr.roleid
;

COMMIT;
