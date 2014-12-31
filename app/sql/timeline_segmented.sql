BEGIN;

DROP VIEW timeline CASCADE;

CREATE VIEW timeline_notes AS
SELECT
n.id, 'note'::varchar(20) as type, n.usercompanyid, n.lastupdated as when,
n.title, n.note as body,
''::varchar as email_from, ''::varchar as email_to,
n.created, n.lastupdated, n.lastupdated as received, n.lastupdated as due, n.lastupdated as completed,
n.private, false as overdue, null::varchar(20) as direction,
n.owner, n.owner as assigned_to, n.owner as assigned_by,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id
FROM notes n
WHERE NOT n.deleted
;

CREATE VIEW timeline_emails AS
SELECT DISTINCT
e.id, 'email'::varchar(20) as type, e.usercompanyid, e.received as when,
e.subject as title, e.body,
e.email_from, e.email_to,
e.created, e.created as lastupdated, e.received, e.received as due, e.received as completed,
false as private, false as overdue,
CASE
WHEN pcm.contact::text = e.email_from THEN 'outgoing'::varchar(20)
WHEN pcm.contact::text = e.email_to THEN 'incoming'::varchar(20)
ELSE ''::varchar(20)
END AS direction,
e.owner, e.owner as assigned_to, e.owner as assigned_by,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id
FROM emails e
LEFT JOIN users u ON u.username::text = e.owner::text
LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from = pcm.contact OR e.email_to = pcm.contact)
;

CREATE VIEW timeline_flags AS
SELECT
f.id, 'flag'::varchar(20) as type, f.usercompanyid, f.created as when,
f.title, f.title as body,
''::varchar as email_from, ''::varchar as email_to,
f.created, f.created as lastupdated, f.created as received, f.created as due, f.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
f.owner, f.owner as assigned_to, f.owner as assigned_by,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id
FROM flags f
;

CREATE VIEW timeline_s3_files AS
SELECT
s3.id, 's3file'::varchar(20) as type, s3.usercompanyid, s3.created as when,
s3.filename as title, s3.filename as body,
''::varchar as email_from, ''::varchar as email_to,
s3.created, s3.created as lastupdated, s3.created as received, s3.created as due, s3.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
s3.owner, s3.owner as assigned_to, s3.owner as assigned_by,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id
FROM s3_files s3
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'
;

CREATE VIEW timeline_opportunities AS
SELECT
o.id, 'opportunity'::varchar(20) as type, o.usercompanyid, o.created as when,
o.name as title, o.description as body,
''::varchar as email_from, ''::varchar as email_to,
o.created, o.created as lastupdated, o.created as received, o.created as due, o.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
o.owner, o.owner as assigned_to, o.owner as assigned_by,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id
FROM opportunities o
WHERE o.archived = FALSE
;

CREATE VIEW timeline_activities_new AS 
SELECT
na.id, 'new_activity'::varchar(20) as type, na.usercompanyid, na.created as when,
na.name as title, na.description as body,
''::varchar as email_from, ''::varchar as email_to,
na.created, na.created as lastupdated, na.created as received, na.due, na.completed,
false as private, na.overdue, null::varchar(20) as direction,
na.owner, na.assigned_to as assigned_to, na.owner as assigned_by,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id
FROM tactile_activities_overview na
WHERE na.completed IS NULL AND NOT na.overdue
;

CREATE VIEW timeline_activities_completed AS
SELECT
ca.id, 'completed_activity'::varchar(20) as type, ca.usercompanyid, ca.completed as when,
ca.name as title, ca.description as body,
''::varchar as email_from, ''::varchar as email_to,
ca.created, ca.created as lastupdated, ca.created as received, ca.due, ca.completed,
false as private, ca.overdue, null::varchar(20) as direction,
ca.owner, ca.assigned_to as assigned_to, ca.owner as assigned_by,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id
FROM tactile_activities_overview ca
WHERE ca.completed IS NOT NULL
;

CREATE VIEW timeline_activities_overdue AS
SELECT
oa.id, 'overdue_activity'::varchar(20) as type, oa.usercompanyid, oa.due as when,
oa.name as title, oa.description as body,
''::varchar as email_from, ''::varchar as email_to,
oa.created, oa.created as lastupdated, oa.created as received, oa.due, oa.completed,
false as private, oa.overdue, null::varchar(20) as direction,
oa.owner, oa.assigned_to as assigned_to, oa.owner as assigned_by,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id
FROM tactile_activities_overview oa
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;

COMMIT;