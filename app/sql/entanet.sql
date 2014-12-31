ALTER TABLE tactile_accounts ADD entanet_domain varchar;
ALTER TABLE tactile_accounts ADD entanet_code varchar;

CREATE TABLE entanet_extensions (
	username varchar not null references users(username) on update cascade on delete cascade,
	extension varchar not null,
	usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

create index person_contact_methods_contact_normalize on person_contact_methods(replace(contact,' ',''));
create index company_contact_methods_contact_normalize on company_contact_methods(replace(contact,' ',''));