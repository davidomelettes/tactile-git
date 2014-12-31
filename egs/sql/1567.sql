begin;
DROP VIEW personoverview;
CREATE VIEW personoverview AS
SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, c.name AS company, (((((((COALESCE(p.title, ''::character varying)::text || ' '::text) || p.firstname::text) || ' '::text) || COALESCE(p.middlename, ''::character varying)::text) || ' '::text) || p.surname::text) || ' '::text) || COALESCE(p.suffix, ''::character varying)::text AS fullname, ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, hr.username AS usernameaccess
   FROM person p
   LEFT JOIN company c ON c.id = p.company_id
   LEFT JOIN person_contact_methods ph ON p.id = ph.person_id AND ph.main AND ph."type" = 'T'::bpchar
   LEFT JOIN person_contact_methods fa ON p.id = fa.person_id AND fa.main AND fa."type" = 'F'::bpchar
   LEFT JOIN person_contact_methods mo ON p.id = mo.person_id AND mo.main AND mo."type" = 'M'::bpchar
   LEFT JOIN person_contact_methods e ON p.id = e.person_id AND e.main AND e."type" = 'E'::bpchar
   LEFT JOIN companyroles cr ON cr.companyid = p.company_id AND cr."read"
   LEFT JOIN hasrole hr ON hr.roleid = cr.roleid;


DROP VIEW companyoverview;
CREATE VIEW companyoverview AS
SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, 
hr.username AS usernameaccess
   FROM company c
   LEFT JOIN companyaddress ca ON c.id = ca.company_id AND ca.main
   LEFT JOIN company_contact_methods p ON c.id = p.company_id AND p.main AND p."type" = 'T'::bpchar
   LEFT JOIN company_contact_methods f ON c.id = f.company_id AND f.main AND f."type" = 'F'::bpchar
   LEFT JOIN company_contact_methods e ON c.id = e.company_id AND e.main AND e."type" = 'E'::bpchar
   LEFT JOIN companyroles cr ON c.id = cr.companyid AND cr."read"
   LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
;
commit;
