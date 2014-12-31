BEGIN;
CREATE INDEX person_usercompanyid ON person(usercompanyid);
CREATE INDEX person_owner ON person(owner);
CREATE INDEX person_company_id ON person(company_id);

CREATE INDEX personaddress_person_id ON personaddress(person_id);
CREATE INDEX personaddress_for_overview ON personaddress(person_id,main);

CREATE INDEX companyroles_companyid_read ON companyroles(companyid,read) WHERE read;

drop index hasrole_username_index;

create index hasrole_roleid_username ON hasrole(roleid,username);
COMMIT;
