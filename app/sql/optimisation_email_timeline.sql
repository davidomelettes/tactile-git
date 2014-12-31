BEGIN;

update s3_files set email_id = null where id in (select f.id from s3_files f left join emails e on e.id=f.email_id where f.email_id is not null and e.id is null);

alter table s3_files add constraint s3_files_email_id_fkey FOREIGN KEY (email_id) references emails(id) ON UPDATE CASCADE ON DELETE SET NULL;

DROP VIEW timeline_email_attachment_count CASCADE;
CREATE VIEW timeline_email_attachment_count AS
SELECT s3.email_id, s3.organisation_id, s3.person_id, count(*) AS count FROM s3_files s3 WHERE s3.email_id IS NOT NULL GROUP BY s3.email_id, s3.organisation_id, s3.person_id;

CREATE VIEW timeline_emails AS
SELECT DISTINCT e.id, 'email'::character varying(20) AS type, e.usercompanyid, e.received AS "when", e.subject AS title, e.body, e.email_from, e.email_to, 0::int AS email_attachments, e.created, e.created AS lastupdated, e.received, e.received AS due, e.received AS completed, false AS private, false AS overdue, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::character varying(20)
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::character varying(20)
            ELSE ''::character varying(20)
        END AS direction, e.owner, e.owner AS assigned_to, e.owner AS assigned_by, e.organisation_id, e.person_id, e.opportunity_id, e.activity_id, (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
   FROM emails e
   LEFT JOIN people p ON e.person_id = p.id
   LEFT JOIN organisations o ON e.organisation_id = o.id
   LEFT JOIN opportunities opp ON e.opportunity_id = opp.id
   LEFT JOIN tactile_activities act ON e.activity_id = act.id
   LEFT JOIN users u ON u.username::text = e.owner::text
   LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text);

COMMIT;
