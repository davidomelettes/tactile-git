begin;
alter table company alter column accountnumber drop not null;
alter table company add column is_lead boolean not null default false;
DROP VIEW companyoverview;
CREATE VIEW companyoverview AS SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, 
c.website, c.employees, 
c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, 
ca.street1, 
ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, 
f.contact AS fax,
        CASE
            WHEN hr.username IS NULL THEN c."owner"
            ELSE hr.username
        END AS usernameaccess
   FROM company c
   LEFT JOIN companyaddress ca ON c.id = ca.company_id AND ca.main
   LEFT JOIN company_contact_methods p ON c.id = p.company_id AND p.main AND p."type" = 'T'::bpchar
   LEFT JOIN company_contact_methods f ON c.id = f.company_id AND f.main AND f."type" = 'F'::bpchar
   LEFT JOIN company_contact_methods e ON c.id = e.company_id AND e.main AND e."type" = 'E'::bpchar
   LEFT JOIN companyroles cr ON c.id = cr.companyid AND cr."read"
   LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
UNION
 SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, 
c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, 
ca.street1, 
ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, 
f.contact AS fax, c."owner" AS usernameaccess
   FROM company c
   LEFT JOIN companyaddress ca ON c.id = ca.company_id AND ca.main
   LEFT JOIN company_contact_methods p ON c.id = p.company_id AND p.main AND p."type" = 'T'::bpchar
   LEFT JOIN company_contact_methods f ON c.id = f.company_id AND f.main AND f."type" = 'F'::bpchar
   LEFT JOIN company_contact_methods e ON c.id = e.company_id AND e.main AND e."type" = 'E'::bpchar;
COMMIT;
