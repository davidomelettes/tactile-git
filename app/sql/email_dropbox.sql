DROP VIEW email_overview;

CREATE VIEW email_overview AS
SELECT 
	e.id, e.person_id, e.company_id, e.email_from, e.email_to, e.subject, e.body, e.received, e.created, e."owner", e.usercompanyid, c.name AS company, 
	(p.firstname::text || ' '::text) || p.surname::text AS person, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'incoming'::text
            WHEN pcm.contact::text = e.email_to::text THEN 'outgoing'::text
            ELSE ''::text
        END AS direction
	FROM emails e
		LEFT JOIN person_contact_methods pcm ON e.person_id = pcm.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
		LEFT JOIN person p ON e.person_id = p.id
		LEFT JOIN company c ON e.company_id = c.id;
