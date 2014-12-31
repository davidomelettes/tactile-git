BEGIN;
CREATE VIEW poll_options_overview AS
SELECT o.id, o.name, o.description, o.poll_id, p.name AS poll,o.usercompanyid, o.created, o.lastupdated, count(v.id) AS votes 
FROM poll_options o LEFT JOIN poll_votes v ON (v.option_id=o.id) LEFT JOIN polls p ON (p.id=o.poll_id) 
group by o.id, o.name, o.description, o.poll_id, p.name,o.usercompanyid, o.created, o.lastupdated;
COMMIT;