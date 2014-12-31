BEGIN;

DROP VIEW tactile_activities_overview CASCADE;

CREATE VIEW tactile_activities_overview AS
SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id, a.organisation_id, a.person_id, a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to, a.assigned_by, a.owner, a.alteredby, a.created, a.lastupdated, a.usercompanyid, t.name AS type, o.name AS opportunity, org.name AS organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, 
        CASE
            WHEN a.later = true THEN false
            WHEN a."time" IS NULL THEN a.date < now()::date
            ELSE (a.date + a."time") < now()
        END AS overdue, 
        CASE
            WHEN a.later = true THEN 'infinity'::timestamp without time zone
            WHEN a."time" IS NULL THEN a.date + '23:59:59'::time without time zone
            ELSE a.date + a."time"
        END AS due
   FROM tactile_activities a
   LEFT JOIN activitytype t ON t.id = a.type_id
   LEFT JOIN opportunities o ON o.id = a.opportunity_id
   LEFT JOIN organisations org ON org.id = a.organisation_id
   LEFT JOIN people p ON p.id = a.person_id
   LEFT JOIN users u ON u.username::text = a.assigned_to::text;

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

COMMIT;
