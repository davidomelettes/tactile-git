BEGIN;
DROP VIEW email_overview;

CREATE VIEW email_overview AS
SELECT e.id, e.person_id, e.company_id, e.email_from, e.email_to, e.subject, e.body, e.received, e.created,
	e."owner", e.usercompanyid, c.name AS company, 
	(p.firstname::text || ' '::text) || p.surname::text AS person,
	CASE WHEN pcm.contact=e.email_from THEN 'incoming' ELSE 'outgoing' END AS direction
   FROM emails e
	JOIN person_contact_methods pcm ON (e.person_id=pcm.person_id AND (e.email_from=pcm.contact OR e.email_to=pcm.contact))
   LEFT JOIN person p ON e.person_id = p.id
   LEFT JOIN company c ON e.company_id = c.id;

create index emails_usercompanyid on emails(usercompanyid);
create index notes_opportunity_id on notes(opportunity_id);
create index notes_recent on notes (usercompanyid, lastupdated);
create index emails_recent on emails(received);
create index recently_viewed_created on recently_viewed (created);
analyze emails;
analyze notes;
analyze recently_viewed;
COMMIT;

