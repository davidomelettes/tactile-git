BEGIN;
DROP VIEW tactile_activities_overview;
CREATE VIEW tactile_activities_overview AS
SELECT a.*,
t.name AS type,
o.name AS opportunity,
c.name AS company,
p.firstname || ' ' || p.surname AS person,
CASE WHEN later=true THEN false
WHEN "time" is null then "date"<now()::date
ELSE (date+time) < now()::timestamp at time zone u.timezone END AS overdue,
CASE WHEN later=true THEN 'infinity'::timestamp
WHEN "time" is null then date+'23:59:59'::time
ELSE date+time END AS due
FROM tactile_activities a
LEFT JOIN activitytype t ON (t.id=a.type_id)
LEFT JOIN opportunities o ON (o.id=a.opportunity_id)
LEFT JOIN company c ON (c.id=a.company_id)
LEFT JOIN person p ON (p.id=a.person_id)
LEFT JOIN users u ON (u.username=a.assigned_to);
COMMIT;