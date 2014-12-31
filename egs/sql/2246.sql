BEGIN;
DROP VIEW opportunitiesoverview;

CREATE VIEW opportunitiesoverview AS
SELECT o.id, o.status_id, o.campaign_id, o.company_id, o.person_id, o."owner", o.name, o.description, o.cost, o.probability, o.enddate, o.usercompanyid, o.type_id, o.source_id, o.nextstep, o.assigned, o.created, o.lastupdated, o.alteredby, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person, cam.name AS campaign, os.name AS source, ot.name AS "type", opportunitystatus.name AS status, 
        CASE
            WHEN opportunitystatus.* IS NULL THEN false
            ELSE opportunitystatus.open
        END AS open,
		CASE
			WHEN opportunitystatus.* IS NULL THEN false
			ELSE opportunitystatus.won
		END AS won
   FROM opportunities o
   LEFT JOIN company c ON o.company_id = c.id
   LEFT JOIN person p ON o.person_id = p.id
   LEFT JOIN campaigns cam ON o.campaign_id = cam.id
   LEFT JOIN opportunitysource os ON o.source_id = os.id
   LEFT JOIN opportunitytype ot ON o.type_id = ot.id
   LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id;
COMMIT;