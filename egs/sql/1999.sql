BEGIN;
CREATE TABLE polls(
id serial not null primary key,
name varchar not null,
description text,
website_id int not null references websites(id) on update cascade on delete cascade,
active boolean not null default true,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now()
);

CREATE TABLE poll_options(
id serial not null primary key,
name varchar not null,
description text,
image bigint references file(id) on update cascade on delete set null,
poll_id int not null references polls(id) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now()
);

CREATE TABLE poll_votes(
id serial not null primary key,
option_id int not null references poll_options(id) on update cascade on delete cascade,
ip_address inet not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now()
);

alter table websites add polls boolean not null default false;
COMMIT;