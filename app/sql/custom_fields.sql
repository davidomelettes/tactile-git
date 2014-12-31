BEGIN;

create table custom_fields(
id serial primary key,
usercompanyid bigint REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
name varchar not null,
type varchar(2) not null,
organisations boolean default false,
people boolean default false,
opportunities boolean default false,
activities boolean default false,
created timestamp not null default now()
);

create table custom_field_options(
id serial primary key,
field_id bigint not null REFERENCES custom_fields(id) ON UPDATE CASCADE ON DELETE CASCADE,
value text,
UNIQUE (field_id, value)
);

create table custom_field_map(
id serial primary key,
field_id bigint REFERENCES custom_fields(id) ON UPDATE CASCADE ON DELETE CASCADE,
organisation_id bigint REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
person_id bigint REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
opportunity_id bigint REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE,
activity_id bigint REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE,
hash varchar not null,
value text,
enabled boolean, 
option bigint REFERENCES custom_field_options(id) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE (field_id, organisation_id),
UNIQUE (field_id, person_id),
UNIQUE (field_id, opportunity_id),
UNIQUE (field_id, activity_id)
);

CREATE VIEW custom_field_map_overview as
SELECT m.*, f.name, f.type, o.value as option_name
FROM custom_field_map m 
	LEFT JOIN custom_fields f ON m.field_id = f.id
	LEFT JOIN custom_field_options o ON m.option = o.id;

-- Convert under-used organisation fields to custom fields 
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Employees'::varchar, 'n'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Employees') as field_id, id, 'org'||id::varchar as hash, employees FROM organisations o WHERE o.employees IS NOT NULL;
ALTER TABLE organisations DROP COLUMN employees;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Credit Limit'::varchar, 'n'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Credit Limit') as field_id, id, 'org'||id::varchar as hash, creditlimit FROM organisations o WHERE o.creditlimit IS NOT NULL;
ALTER TABLE organisations DROP COLUMN creditlimit;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'VAT Number'::varchar, 't'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'VAT Number') as field_id, id, 'org'||id::varchar as hash, vatnumber FROM organisations o WHERE o.vatnumber IS NOT NULL;
ALTER TABLE organisations DROP COLUMN vatnumber;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Company Number'::varchar, 't'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Company Number') as field_id, id, 'org'||id::varchar as hash, companynumber FROM organisations o WHERE o.companynumber IS NOT NULL;
ALTER TABLE organisations DROP COLUMN companynumber;

COMMIT;
