BEGIN;

CREATE TABLE flags (
id SERIAL PRIMARY KEY,
person_id INT REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
organisation_id INT REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
opportunity_id INT REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE,
activity_id INT REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE,
title VARCHAR NOT NULL,
created TIMESTAMP NOT NULL DEFAULT now(),
owner VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE VIEW flags_overview AS
SELECT f.id, f.person_id, f.organisation_id, f.opportunity_id, f.activity_id, f.title, f.created, f.owner, f.usercompanyid,
org.name as organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, opp.name as opportunity, act.name as activity
FROM flags f
LEFT JOIN people p ON f.person_id = p.id
LEFT JOIN organisations org ON f.organisation_id = org.id
LEFT JOIN opportunities opp ON f.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON f.activity_id = act.id;

COMMIT;