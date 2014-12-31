BEGIN;

CREATE VIEW search_overview AS
SELECT usercompanyid, 'organisations' AS type, org.id, name, assigned_to, owner, org.id as organisation_id, orghr.username, false as private
	FROM organisations org
	LEFT JOIN organisation_roles orgr ON org.id = orgr.organisation_id AND orgr.read
	LEFT JOIN hasrole orghr ON orgr.roleid = orghr.roleid
UNION SELECT usercompanyid, 'people' AS type, p.id, firstname || ' ' || surname as name, assigned_to, owner, p.organisation_id, phr.username, private
	FROM people p
	LEFT JOIN organisation_roles pr ON p.organisation_id = pr.organisation_id AND pr.read
	LEFT JOIN hasrole phr ON pr.roleid = phr.roleid
UNION SELECT usercompanyid, 'opportunities' AS type, opp.id, name, assigned_to, owner, opp.organisation_id, opphr.username, false as private
	FROM opportunities opp
	LEFT JOIN organisation_roles oppr ON opp.organisation_id = oppr.organisation_id AND oppr.read
	LEFT JOIN hasrole opphr ON oppr.roleid = opphr.roleid
UNION SELECT usercompanyid, 'activities' AS type, act.id, name, assigned_to, owner, act.organisation_id, acthr.username, false as private
	FROM tactile_activities act
	LEFT JOIN organisation_roles actr ON act.organisation_id = actr.organisation_id AND actr.read
	LEFT JOIN hasrole acthr ON actr.roleid = acthr.roleid
;

COMMIT;