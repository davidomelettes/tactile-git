CREATE VIEW email_overview AS
SELECT e.*, c.name AS company, p.firstname || ' ' || p.surname AS person FROM emails e
LEFT JOIN person p ON (e.person_id=p.id)
LEFT JOIN company c ON (e.company_id=c.id);