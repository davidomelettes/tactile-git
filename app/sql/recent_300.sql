create table recently_viewed (
id serial primary key,
owner varchar not null references users(username) on update cascade on delete cascade,
label varchar not null,
type varchar not null,
link_id int not null,
created timestamp not null default now()
);