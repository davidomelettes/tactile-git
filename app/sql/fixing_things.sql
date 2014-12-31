BEGIN;

ALTER TABLE company RENAME COLUMN assigned TO assigned_to;
ALTER TABLE opportunities RENAME COLUMN assigned TO assigned_to;

ALTER TABLE company RENAME TO organisations;
ALTER TABLE company_contact_methods RENAME TO organisation_contact_methods;
ALTER TABLE companyroles RENAME TO organisation_roles;

ALTER TABLE organisations ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE organisations ALTER COLUMN lastupdated TYPE timestamp(0);

-- foobaroverview to foobar_overview?
--ALTER VIEW companyoverview RENAME TO organisations_overview;
--ALTER VIEW companyrolesoverview RENAME TO organisation_roles_overview;

ALTER TABLE tactile_activities RENAME COLUMN company_id TO organisation_id;
DROP VIEW tactile_activities_overview;
ALTER TABLE tactile_activities ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE tactile_activities ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW tactile_activities_overview AS
SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id,
 a.organisation_id, a.person_id, a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to,
  a.assigned_by, a.owner, a.alteredby, a.created, a.lastupdated, a.usercompanyid, t.name AS type,
   o.name AS opportunity, org.name AS organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, 
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
   LEFT JOIN organisations org ON org.id = a.organisation_id
   LEFT JOIN person p ON p.id = a.person_id
   LEFT JOIN users u ON u.username::text = a.assigned_to::text;

ALTER TABLE opportunities RENAME COLUMN company_id TO organisation_id;
DROP VIEW opportunitiesoverview;
ALTER TABLE opportunities ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE opportunities ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW opportunities_overview AS
SELECT o.id, o.status_id, o.campaign_id, o.organisation_id, o.person_id, o.owner, o.name,
 o.description, o.cost, o.probability, o.enddate, o.usercompanyid, o.type_id, o.source_id,
  o.nextstep, o.assigned_to, o.created, o.lastupdated, o.alteredby, o.archived, org.name AS organisation,
   (p.firstname::text || ' '::text) || p.surname::text AS person, os.name AS source, ot.name AS type,
    opportunitystatus.name AS status, COALESCE(opportunitystatus.open, false) AS open, 
    COALESCE(opportunitystatus.won, false) AS won
   FROM opportunities o
   LEFT JOIN organisations org ON o.organisation_id = org.id
   LEFT JOIN person p ON o.person_id = p.id
   LEFT JOIN opportunitysource os ON o.source_id = os.id
   LEFT JOIN opportunitytype ot ON o.type_id = ot.id
   LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id;


ALTER TABLE person RENAME COLUMN company_id TO organisation_id;
ALTER TABLE person RENAME COLUMN lang TO language_code;
ALTER TABLE person RENAME TO people;
DROP VIEW personoverview;
DROP VIEW personoverview2;
ALTER TABLE people ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE people ALTER COLUMN lastupdated TYPE timestamp(0);

--ALTER TABLE tickets RENAME COLUMN company_id TO organisation_id;

ALTER TABLE organisation_contact_methods RENAME COLUMN company_id TO organisation_id;
ALTER TABLE organisation_roles RENAME COLUMN companyid TO organisation_id;

-- email_overview to emails_overview?
ALTER TABLE emails RENAME COLUMN company_id TO organisation_id;
DROP VIEW email_overview;
ALTER TABLE emails ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE emails ALTER COLUMN received TYPE timestamp(0);
CREATE VIEW emails_overview AS 
SELECT e.id, e.person_id, e.organisation_id, e.opportunity_id, e.email_from, e.email_to, e.subject,
 e.body, e.received, e.created, e.owner, e.usercompanyid, org.name AS organisation,
  (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::text
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::text
            ELSE ''::text
        END AS direction
   FROM emails e
   LEFT JOIN users u ON u.username::text = e.owner::text
   LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
   LEFT JOIN people p ON e.person_id = p.id
   LEFT JOIN organisations org ON e.organisation_id = org.id
   LEFT JOIN opportunities o ON e.opportunity_id = o.id;

ALTER TABLE notes RENAME COLUMN company_id TO organisation_id;
DROP VIEW notes_overview;
ALTER TABLE notes ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE notes ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW notes_overview AS
SELECT n.id, n.title, n.note, n.organisation_id, n.person_id, n.opportunity_id, 
n.activity_id, n.project_id, n.ticket_id, n.owner, n.alteredby, n.created,
n.lastupdated, n.usercompanyid, n.private, n.deleted, org.name AS organisation,
(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, a.name AS activity
FROM notes n
LEFT JOIN organisations org ON org.id = n.organisation_id
LEFT JOIN people p ON p.id = n.person_id
LEFT JOIN opportunities o ON o.id = n.opportunity_id
LEFT JOIN tactile_activities a ON a.id = n.activity_id;

ALTER TABLE s3_files RENAME COLUMN company_id TO organisation_id;
DROP VIEW s3_files_overview;
CREATE VIEW s3_files_overview AS
SELECT f.id, f.bucket, f.object, f.filename, f.content_type, f.size,
 f.extension, f.comment, f.owner, f.created, f.usercompanyid, f.organisation_id,
  org.name AS organisation, f.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person,
   f.opportunity_id, o.name AS opportunity, f.activity_id, a.name AS activity, f.email_id, 
   e.subject AS email, f.ticket_id, f.changeset_id
   FROM s3_files f
   LEFT JOIN organisations org ON org.id = f.organisation_id
   LEFT JOIN people p ON p.id = f.person_id
   LEFT JOIN opportunities o ON o.id = f.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = f.activity_id
   LEFT JOIN emails e ON e.id = f.email_id;

ALTER TABLE tactile_accounts RENAME company_id TO organisation_id;
ALTER TABLE user_company_access RENAME company_id TO organisation_id;

ALTER TABLE tag_map RENAME company_id TO organisation_id;

DROP VIEW user_company_accessoverview;
CREATE VIEW user_company_access_overview AS
SELECT u.username, u.organisation_id, u.id, u.enabled, org.name AS organisation
   FROM user_company_access u
   LEFT JOIN organisations org ON u.organisation_id = org.id;

ALTER TABLE system_companies RENAME COLUMN company_id TO organisation_id;

DROP TABLE calendar_events CASCADE;
DROP TABLE cb_relations CASCADE;
DROP VIEW companyrolesoverview;
DROP TABLE project_notes CASCADE;
DROP TABLE project_issues CASCADE;
DROP TABLE task_resources CASCADE;
DROP TABLE task_notes CASCADE;
DROP TABLE tasks CASCADE;
DROP TABLE expenses CASCADE;
DROP TABLE projects CASCADE;
DROP TABLE plmaster CASCADE;
DROP TABLE websites CASCADE;

DROP TABLE store_suppliers CASCADE;
DROP TABLE slmaster CASCADE;

DROP TABLE company_notes CASCADE;
DROP TABLE companies_in_categories;
DROP TABLE companyparams;
DROP TABLE companypermissions CASCADE;
DROP TABLE haspermission;
DROP TABLE permissions;
DROP TABLE activity_notes;
DROP TABLE activities CASCADE;

ALTER TABLE company_id_seq RENAME TO organisations_id_seq;
ALTER TABLE person_id_seq RENAME TO people_id_seq;
ALTER TABLE company_contact_methods_id_seq RENAME TO organisation_contact_methods_id_seq; 
ALTER TABLE companyroles_id_seq RENAME TO organisation_roles_id_seq; 

-- fix tags!
UPDATE tag_map SET hash = 'peo'||substring(hash from '[0-9]+$') where hash like 'p%';
UPDATE tag_map SET hash = 'opp'||substring(hash from '[0-9]+$') where hash like 'o%';
UPDATE tag_map SET hash = 'act'||substring(hash from '[0-9]+$') where hash like 'a%';
UPDATE tag_map SET hash = 'org'||substring(hash from '[0-9]+$') where hash like 'c%';

COMMIT;
