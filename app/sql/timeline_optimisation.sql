BEGIN;


DROP VIEW timeline_activities_completed;
CREATE VIEW timeline_activities_completed AS
SELECT
	ca.id, 'completed_activity'::character varying(20) AS type, ca.usercompanyid, ca.completed AS "when", ca.name AS title, ca.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	ca.created, ca.lastupdated, ca.created AS received, ca.due, ca.completed,
	false AS private, ca.overdue, NULL::character varying(20) AS direction,
	ca.owner, ca.alteredby, ca.assigned_to, ca.owner AS assigned_by,
	ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview ca
LEFT JOIN people p ON ca.person_id = p.id
LEFT JOIN organisations org ON ca.organisation_id = org.id
LEFT JOIN opportunities opp ON ca.opportunity_id = opp.id
WHERE ca.completed IS NOT NULL;


DROP VIEW timeline_activities_new;
CREATE VIEW timeline_activities_new AS
SELECT
	na.id, 'new_activity'::character varying(20) AS type, na.usercompanyid, na.created AS "when", na.name AS title, na.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	na.created, na.lastupdated, na.created AS received, na.due, na.completed,
	false AS private, na.overdue, NULL::character varying(20) AS direction,
	na.owner, na.alteredby, na.assigned_to, na.owner AS assigned_by,
	na.organisation_id, na.person_id, na.opportunity_id, na.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview na
LEFT JOIN people p ON na.person_id = p.id
LEFT JOIN organisations org ON na.organisation_id = org.id
LEFT JOIN opportunities opp ON na.opportunity_id = opp.id
WHERE na.completed IS NULL AND NOT na.overdue;


DROP VIEW timeline_activities_overdue;
CREATE VIEW timeline_activities_overdue AS
SELECT
	oa.id, 'overdue_activity'::character varying(20) AS type, oa.usercompanyid, oa.due AS "when", oa.name AS title, oa.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	oa.created, oa.lastupdated, oa.created AS received, oa.due, oa.completed,
	false AS private, oa.overdue, NULL::character varying(20) AS direction,
	oa.owner, oa.alteredby, oa.assigned_to, oa.owner AS assigned_by,
	oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview oa
LEFT JOIN people p ON oa.person_id = p.id
LEFT JOIN organisations org ON oa.organisation_id = org.id
LEFT JOIN opportunities opp ON oa.opportunity_id = opp.id
WHERE oa.completed IS NULL AND oa.overdue AND oa.class::text <> 'event'::text;


DROP VIEW timeline_emails;
CREATE VIEW timeline_emails AS
SELECT
	e.id, 'email'::character varying(20) AS type, e.usercompanyid, e.received AS "when", e.subject AS title, e.body,
	e.email_from, e.email_to, 0 AS email_attachments, 0 as size,
	e.created, e.created AS lastupdated, e.received, e.received AS due, e.received AS completed,
	false AS private, false AS overdue, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::character varying(20)
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::character varying(20)
            ELSE ''::character varying(20)
        END AS direction,
	e.owner, e.owner AS alteredby, e.owner AS assigned_to, e.owner AS assigned_by,
	e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM emails e
LEFT JOIN people p ON e.person_id = p.id
LEFT JOIN organisations o ON e.organisation_id = o.id
LEFT JOIN opportunities opp ON e.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON e.activity_id = act.id
LEFT JOIN users u ON u.username::text = e.owner::text
LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text);


DROP VIEW timeline_flags;
CREATE VIEW timeline_flags AS
SELECT
	f.id, 'flag'::character varying(20) AS type, f.usercompanyid, f.created AS "when", f.title, f.title AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	f.created, f.created AS lastupdated, f.created AS received, f.created AS due, f.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	f.owner, f.owner AS alteredby, f.owner AS assigned_to, f.owner AS assigned_by,
	f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM flags f
LEFT JOIN people p ON f.person_id = p.id
LEFT JOIN organisations o ON f.organisation_id = o.id
LEFT JOIN opportunities opp ON f.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON f.activity_id = act.id;


DROP VIEW timeline_notes;
CREATE VIEW timeline_notes AS
SELECT
	n.id, 'note'::character varying(20) AS type, n.usercompanyid, n.lastupdated AS "when", n.title, n.note AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	n.created, n.lastupdated, n.lastupdated AS received, n.lastupdated AS due, n.lastupdated AS completed,
	n.private, false AS overdue, NULL::character varying(20) AS direction,
	n.owner, n.alteredby, n.owner AS assigned_to, n.owner AS assigned_by,
	n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM notes n
LEFT JOIN people p ON n.person_id = p.id
LEFT JOIN organisations o ON n.organisation_id = o.id
LEFT JOIN opportunities opp ON n.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON n.activity_id = act.id
WHERE NOT n.deleted;


DROP VIEW timeline_opportunities;
CREATE VIEW timeline_opportunities AS
SELECT
	o.id, 'opportunity'::character varying(20) AS type, o.usercompanyid, o.created AS "when", o.name AS title, o.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	o.created, o.created AS lastupdated, o.created AS received, o.created AS due, o.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	o.owner, o.alteredby, o.owner AS assigned_to, o.owner AS assigned_by,
	o.organisation_id, o.person_id, o.id AS opportunity_id, o.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, ''::character varying AS opportunity, ''::character varying AS activity
FROM opportunities o
LEFT JOIN people p ON o.person_id = p.id
LEFT JOIN organisations org ON o.organisation_id = org.id
WHERE o.archived = false;


DROP VIEW timeline_s3_files;
CREATE VIEW timeline_s3_files AS
SELECT
	s3.id, 's3file'::character varying(20) AS type, s3.usercompanyid, s3.created AS "when", s3.filename AS title, s3.filename AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, s3.size,
	s3.created, s3.created AS lastupdated, s3.created AS received, s3.created AS due, s3.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	s3.owner, s3.owner AS alteredby, s3.owner AS assigned_to, s3.owner AS assigned_by,
	s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM s3_files s3
LEFT JOIN people p ON s3.person_id = p.id
LEFT JOIN organisations o ON s3.organisation_id = o.id
LEFT JOIN opportunities opp ON s3.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON s3.activity_id = act.id
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket::text <> 'tactile_public'::text;


COMMIT;
