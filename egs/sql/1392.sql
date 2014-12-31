begin;
drop view useroverview;
create view useroverview as 
select u.*, uca.company_id as usercompanyid, p.firstname || ' ' || p.surname as person from users u left join 
user_company_access uca on u.username=uca.username left join person p on u.person_id=p.id;
commit;
