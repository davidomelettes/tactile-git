BEGIN;

ALTER TABLE emails ADD COLUMN activity_id INT REFERENCES tactile_activities(id);

CREATE VIEW timeline AS
SELECT
e.id, 'email' as type, e.usercompanyid, e.subject as title, e.body, e.received as when,
e.owner, null as assigned_to, false as private,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
ehr.username
FROM emails e
LEFT JOIN organisation_roles eor on e.organisation_id = eor.organisation_id AND eor.read
LEFT JOIN hasrole ehr ON eor.roleid = ehr.roleid

UNION SELECT
n.id, 'note' as type, n.usercompanyid, n.title, n.note as body, n.lastupdated as when,
n.owner, null as assigned_to, n.private,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
nhr.username
FROM notes n
LEFT JOIN organisation_roles nor on n.organisation_id = nor.organisation_id AND nor.read
LEFT JOIN hasrole nhr ON nor.roleid = nhr.roleid
WHERE NOT n.deleted

UNION SELECT
f.id, 'flag' as type, f.usercompanyid, f.title, '' as body, f.created as when,
f.owner, null as assigned_to, false as private,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
fhr.username
FROM flags f
LEFT JOIN organisation_roles foroles on f.organisation_id = foroles.organisation_id AND foroles.read
LEFT JOIN hasrole fhr ON foroles.roleid = fhr.roleid
;

COMMIT;