BEGIN;

DROP VIEW IF EXISTS timeline_notes;
DROP VIEW IF EXISTS timeline_emails;
DROP VIEW IF EXISTS timeline_flags;
DROP VIEW IF EXISTS timeline_s3_files;
DROP VIEW IF EXISTS timeline_opportunities;
DROP VIEW IF EXISTS timeline_activities_new;
DROP VIEW IF EXISTS timeline_activities_completed;
DROP VIEW IF EXISTS timeline_activities_overdue;
DROP VIEW IF EXISTS timeline_email_attachment_count;

CREATE VIEW timeline_notes AS
SELECT
n.id, 'note'::varchar(20) as type, n.usercompanyid, n.lastupdated as when,
n.title, n.note as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
n.created, n.lastupdated, n.lastupdated as received, n.lastupdated as due, n.lastupdated as completed,
n.private, false as overdue, null::varchar(20) as direction,
n.owner, n.owner as assigned_to, n.owner as assigned_by,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM notes n
LEFT JOIN people p ON n.person_id=p.id
LEFT JOIN organisations o ON n.organisation_id=o.id
LEFT JOIN opportunities opp on n.opportunity_id = opp.id
LEFT JOIN tactile_activities act on n.activity_id = act.id
WHERE NOT n.deleted
;

CREATE VIEW timeline_email_attachment_count AS
SELECT email_id, count(*) as count
FROM s3_files
WHERE email_id IS NOT NULL
GROUP BY email_id;

CREATE VIEW timeline_emails AS
SELECT DISTINCT
e.id, 'email'::varchar(20) as type, e.usercompanyid, e.received as when,
e.subject as title, e.body,
e.email_from, e.email_to,
ea.count as email_attachments,
e.created, e.created as lastupdated, e.received, e.received as due, e.received as completed,
false as private, false as overdue,
CASE
WHEN pcm.contact::text = e.email_from THEN 'outgoing'::varchar(20)
WHEN pcm.contact::text = e.email_to THEN 'incoming'::varchar(20)
ELSE ''::varchar(20)
END AS direction,
e.owner, e.owner as assigned_to, e.owner as assigned_by,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM emails e
LEFT JOIN people p ON e.person_id=p.id
LEFT JOIN timeline_email_attachment_count ea on e.id = ea.email_id
LEFT JOIN organisations o ON e.organisation_id=o.id
LEFT JOIN opportunities opp on e.opportunity_id = opp.id
LEFT JOIN tactile_activities act on e.activity_id = act.id
LEFT JOIN users u ON u.username::text = e.owner::text
LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from = pcm.contact OR e.email_to = pcm.contact)
;

CREATE VIEW timeline_flags AS
SELECT
f.id, 'flag'::varchar(20) as type, f.usercompanyid, f.created as when,
f.title, f.title as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
f.created, f.created as lastupdated, f.created as received, f.created as due, f.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
f.owner, f.owner as assigned_to, f.owner as assigned_by,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM flags f
LEFT JOIN people p ON f.person_id=p.id
LEFT JOIN organisations o ON f.organisation_id=o.id
LEFT JOIN opportunities opp on f.opportunity_id = opp.id
LEFT JOIN tactile_activities act on f.activity_id = act.id
;

CREATE VIEW timeline_s3_files AS
SELECT
s3.id, 's3file'::varchar(20) as type, s3.usercompanyid, s3.created as when,
s3.filename as title, s3.filename as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
s3.created, s3.created as lastupdated, s3.created as received, s3.created as due, s3.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
s3.owner, s3.owner as assigned_to, s3.owner as assigned_by,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM s3_files s3
LEFT JOIN people p ON s3.person_id=p.id
LEFT JOIN organisations o ON s3.organisation_id=o.id
LEFT JOIN opportunities opp on s3.opportunity_id = opp.id
LEFT JOIN tactile_activities act on s3.activity_id = act.id
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'
;

CREATE VIEW timeline_opportunities AS
SELECT
o.id, 'opportunity'::varchar(20) as type, o.usercompanyid, o.created as when,
o.name as title, o.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
o.created, o.created as lastupdated, o.created as received, o.created as due, o.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
o.owner, o.owner as assigned_to, o.owner as assigned_by,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
''::varchar as opportunity,
''::varchar as activity
FROM opportunities o
LEFT JOIN people p ON o.person_id=p.id
LEFT JOIN organisations org ON o.organisation_id=org.id
WHERE o.archived = FALSE
;

CREATE VIEW timeline_activities_new AS 
SELECT
na.id, 'new_activity'::varchar(20) as type, na.usercompanyid, na.created as when,
na.name as title, na.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
na.created, na.created as lastupdated, na.created as received, na.due, na.completed,
false as private, na.overdue, null::varchar(20) as direction,
na.owner, na.assigned_to as assigned_to, na.owner as assigned_by,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview na
LEFT JOIN people p ON na.person_id=p.id
LEFT JOIN organisations org ON na.organisation_id=org.id
LEFT JOIN opportunities opp on na.opportunity_id = opp.id
WHERE na.completed IS NULL AND NOT na.overdue
;

CREATE VIEW timeline_activities_completed AS
SELECT
ca.id, 'completed_activity'::varchar(20) as type, ca.usercompanyid, ca.completed as when,
ca.name as title, ca.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
ca.created, ca.created as lastupdated, ca.created as received, ca.due, ca.completed,
false as private, ca.overdue, null::varchar(20) as direction,
ca.owner, ca.assigned_to as assigned_to, ca.owner as assigned_by,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview ca
LEFT JOIN people p ON ca.person_id=p.id
LEFT JOIN organisations org ON ca.organisation_id=org.id
LEFT JOIN opportunities opp on ca.opportunity_id = opp.id
WHERE ca.completed IS NOT NULL
;

CREATE VIEW timeline_activities_overdue AS
SELECT
oa.id, 'overdue_activity'::varchar(20) as type, oa.usercompanyid, oa.due as when,
oa.name as title, oa.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
oa.created, oa.created as lastupdated, oa.created as received, oa.due, oa.completed,
false as private, oa.overdue, null::varchar(20) as direction,
oa.owner, oa.assigned_to as assigned_to, oa.owner as assigned_by,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview oa
LEFT JOIN people p ON oa.person_id=p.id
LEFT JOIN organisations org ON oa.organisation_id=org.id
LEFT JOIN opportunities opp on oa.opportunity_id = opp.id
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;

COMMIT;