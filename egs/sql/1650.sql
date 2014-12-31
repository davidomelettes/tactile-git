begin;
create table opportunity_notes (
id bigserial primary key,
title varchar not null,
note text not null,
opportunity_id bigint not null references opportunities(id) on update cascade on delete cascade,
owner varchar not null references users(username) on update cascade on delete cascade,
alteredby varchar not null references users(username) on update cascade on delete cascade,
lastupdated timestamp without time zone not null default now(),
created timestamp without time zone not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade);
create table activity_notes (
id bigserial primary key,
title varchar not null,
note text not null,
activity_id bigint not null references activities(id) on update cascade on delete cascade,
owner varchar not null references users(username) on update cascade on delete cascade,
alteredby varchar not null references users(username) on update cascade on delete cascade,
lastupdated timestamp without time zone not null default now(),
created timestamp without time zone not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade);
commit;
