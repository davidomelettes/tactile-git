begin;
create table galleries (
id bigserial primary key,
name varchar not null,
description text not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
create table gallery_pictures (
id bigserial primary key,
gallery_id bigint not null references galleries(id) on update cascade on delete cascade,
file_id bigint not null references file(id) on update cascade on delete cascade,
owner varchar not null references users(username) on update cascade on delete cascade);
commit;
