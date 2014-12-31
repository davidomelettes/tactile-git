BEGIN;

CREATE VIEW s3_files_overview AS
SELECT f.id, f.bucket, f.object, f.filename, f.content_type, f."size", f.extension, f.comment, f."owner", f.created, f.usercompanyid, f.company_id, c.name AS company, f.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person, f.opportunity_id, o.name AS opportunity, f.activity_id, a.name AS activity, f.email_id, e.subject as email, ticket_id, changeset_id
   FROM s3_files f
   LEFT JOIN company c ON c.id = f.company_id
   LEFT JOIN person p ON p.id = f.person_id
   LEFT JOIN opportunities o ON o.id = f.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = f.activity_id
   LEFT JOIN emails e ON e.id = f.email_id;

COMMIT;
