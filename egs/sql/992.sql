begin;
drop view useroverview;
create view useroverview as SELECT u.username, u."password", 
u.lastcompanylogin, u.person_id, p.firstname::text || ' ' || 
p.surname::text AS person
FROM users u
JOIN person p ON u.person_id = p.id;
commit;
