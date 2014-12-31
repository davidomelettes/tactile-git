BEGIN;
ALTER TABLE emails ADD opportunity_id integer REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE SET null;

DROP VIEW email_overview;
CREATE VIEW email_overview AS
    SELECT e.id, e.person_id, e.company_id, e.opportunity_id, e.email_from, e.email_to, 
           e.subject, e.body, e.received, e.created, e."owner", e.usercompanyid, c.name AS company, 
           (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, 
            CASE
                WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::text
                WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::text
                ELSE ''::text
            END AS direction
    FROM emails e
	LEFT JOIN users u ON (u.username = e.owner)
    LEFT JOIN person_contact_methods pcm ON (pcm.person_id = u.person_id) AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
    LEFT JOIN person p ON e.person_id = p.id
    LEFT JOIN company c ON e.company_id = c.id
    LEFT JOIN opportunities o ON e.opportunity_id = o.id;
COMMIT;
