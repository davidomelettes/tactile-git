begin;
alter table user_company_access add column id bigserial not null;
alter table user_company_access add column enabled boolean not null default true;
create view user_company_accessoverview as SELECT u.username, u.company_id, u.id, u.enabled, c.name AS company
   FROM user_company_access u
   LEFT JOIN company c ON u.company_id = c.id;
create view system_companiesoverview as SELECT sc.id, sc.company_id, sc.enabled, c.name AS company
   FROM system_companies sc
   LEFT JOIN company c ON sc.company_id = c.id;

commit;
