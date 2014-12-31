BEGIN;
CREATE VIEW notes_overview AS
SELECT n.*, c.name AS company, p.firstname || ' ' || p.surname AS person, o.name AS opportunity, a.name AS activity
FROM notes n
LEFT JOIN company c ON (c.id=n.company_id)
LEFT JOIN person p ON (p.id=n.person_id)
LEFT JOIN opportunities o ON (o.id=n.opportunity_id)
LEFT JOIN activities a ON (a.id=n.activity_id);
COMMIT;