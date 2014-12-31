create index recently_viewed_type_id on recently_viewed(type,link_id);
create index hasrole_username on hasrole(username);

create index recently_viewed_owner ON recently_viewed (owner);

create index tags_usercompanyid on tags(usercompanyid);

create index tactile_accounts_company_id on tactile_accounts(company_id);