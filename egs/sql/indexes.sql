BEGIN;
DROP INDEX company_alteredby;
alter table company drop constraint company_pkey;
alter table company add unique (accountnumber,usercompanyid);
drop index company_accountnumber;
drop index company_assigned;
drop index company_companyid;
drop index company_is_lead;
drop index company_name;
drop index company_owner;
drop index company_branchcompanyid;

CREATE INDEX company_overview ON company(usercompanyid,owner,is_lead) WHERE is_lead=false;
CREATE INDEX company_tree ON company(usercompanyid,parent_id);

COMMIT;
