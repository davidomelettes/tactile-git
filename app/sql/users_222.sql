ALTER TABLE users ADD is_admin boolean not null default false;
ALTER TABLE users ADD enabled boolean not null default true;

CREATE VIEW omelette_useroverview AS
SELECT u.*, uca.company_id AS usercompanyid, (p.firstname::text || ' '::text) || p.surname::text AS person
FROM users u
LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
LEFT JOIN person p ON u.person_id = p.id;

UPDATE users set is_admin=true WHERE person_id IN (SELECT min(id)from person group by usercompanyid);