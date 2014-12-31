BEGIN;
	ALTER TABLE tactile_activities ADD class VARCHAR NOT NULL DEFAULT 'todo';
	ALTER TABLE tactile_activities ADD location VARCHAR;
	ALTER TABLE tactile_activities ADD end_date DATE;
	ALTER TABLE tactile_activities ADD end_time TIME;
	DROP VIEW tactile_activities_overview;
	CREATE VIEW tactile_activities_overview AS
		SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id, a.company_id, a.person_id,
			a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to, a.assigned_by,
			a."owner", a.alteredby, a.created, a.lastupdated, a.usercompanyid,
			t.name AS "type", o.name AS opportunity, c.name AS company,
			(p.firstname::text || ' '::text) || p.surname::text AS person,
		CASE
			WHEN a.later = true THEN false
			WHEN a."time" IS NULL THEN a.date < now()::date
			ELSE (a.date + a."time") < timezone(u.timezone::text, now()::timestamp without time zone)
			END AS overdue,
		CASE
			WHEN a.later = true THEN 'infinity'::timestamp without time zone
			WHEN a."time" IS NULL THEN a.date + '23:59:59'::time without time zone
			ELSE a.date + a."time"
			END AS due
		FROM tactile_activities a
		LEFT JOIN activitytype t ON t.id = a.type_id
		LEFT JOIN opportunities o ON o.id = a.opportunity_id
		LEFT JOIN company c ON c.id = a.company_id
		LEFT JOIN person p ON p.id = a.person_id
		LEFT JOIN users u ON u.username::text = a.assigned_to::text;
COMMIT;
