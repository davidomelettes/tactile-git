BEGIN;

--add notes to orders
ALTER TABLE store_orders ADD notes text;

--personoverview is broken, not pulling email addresses properly
DROP VIEW personoverview;

CREATE VIEW personoverview AS
    (SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, 
	p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, 
	c.name AS company, 
	(((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text)
		|| (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text)
		|| (COALESCE(p.suffix, ''::character varying))::text) AS fullname, 
	ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, 
	CASE WHEN (hr.username IS NULL) 
		THEN p."owner" 
		ELSE hr.username
	END AS usernameaccess 
FROM (((((((person p 
	LEFT JOIN company c ON ((c.id = p.company_id))) 
	LEFT JOIN person_contact_methods ph ON 
		((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) 
	LEFT JOIN person_contact_methods fa ON 
		((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) 
	LEFT JOIN person_contact_methods mo ON 
		((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) 
	LEFT JOIN person_contact_methods e ON 
		((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar)))) 
	LEFT JOIN companyroles cr ON 
		(((cr.companyid = p.company_id) AND cr."read"))) 
	LEFT JOIN hasrole hr ON ((hr.roleid = cr.roleid))) 
UNION 
   SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail,
	p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid,
	c.name AS company,
	(((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) 
		|| (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text)
		|| (COALESCE(p.suffix, ''::character varying))::text) AS fullname,
	ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email,
	p."owner" AS usernameaccess 
FROM (((((person p
	LEFT JOIN company c ON ((c.id = p.company_id))) 
	LEFT JOIN person_contact_methods ph 
		ON ((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) 
	LEFT JOIN person_contact_methods fa ON 
		((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) 
	LEFT JOIN person_contact_methods mo ON 
		((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) 
	LEFT JOIN person_contact_methods e ON 
		((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar))))) 

UNION SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail,
p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid,
c.name AS company,
(((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) 
	|| (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text) 
	|| (COALESCE(p.suffix, ''::character varying))::text) AS fullname,
ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email,
c."owner" AS usernameaccess
FROM (((((person p 
	LEFT JOIN company c ON ((c.id = p.company_id))) 
	LEFT JOIN person_contact_methods ph ON 
		((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) 
	LEFT JOIN person_contact_methods fa ON 
		((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) 
	LEFT JOIN person_contact_methods mo ON 
		((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) 
	LEFT JOIN person_contact_methods e ON 
		((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar))));




---and add a customeroverview
CREATE VIEW customeroverview AS
    SELECT DISTINCT c.id, c.person_id,c.username, p.fullname, p.firstname, p.surname, p.phone, p.fax, p.email, p.usercompanyid FROM (customers c JOIN personoverview p ON ((c.person_id = p.id)));
COMMIT;
