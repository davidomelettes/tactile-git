BEGIN;
CREATE TABLE tags (
	id serial not null primary key,
	name varchar not null,
	usercompanyid bigint not null references company(id) on update cascade on delete cascade,
	created timestamp not null default now(),
	unique(name,usercompanyid)
);


CREATE TABLE tag_map (
	tag_id int not null references tags(id) on update cascade on delete cascade,
	created timestamp not null default now(),
	lastupdated timestamp not null default now(),
	company_id int references company(id) on update cascade on delete cascade,
	person_id int references company(id) on update cascade on delete cascade,
	opportunity_id int references company(id) on update cascade on delete cascade,
	activity_id int references company(id) on update cascade on delete cascade
);
CREATE UNIQUE INDEX tag_map_tag_id_company_id ON tag_map (tag_id,company_id) WHERE company_id is not null;
COMMIT;
