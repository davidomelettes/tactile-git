
BEGIN;
DROP VIEW useroverview;
CREATE VIEW useroverview AS
SELECT u.username, u."password", u.enabled, u.lastcompanylogin, u.person_id, uca.company_id AS usercompanyid, (p.firstname::text || ' '::text) || p.surname::text AS person
   FROM users u
   LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
   LEFT JOIN person p ON u.person_id = p.id;
COMMIT;