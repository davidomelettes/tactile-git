BEGIN;
CREATE TABLE intranet_postings(
id serial primary key,
title varchar not null,
type varchar not null,
contents text,
additional text,
office varchar,
owner varchar not null references users(username) on update cascade on delete cascade,
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade
);
COMMIT;