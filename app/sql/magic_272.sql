create table tactile_magic (
id serial not null primary key,
username varchar not null references users(username) on update cascade on delete cascade,
"key" varchar not null,
"value" varchar not null,
unique (username,key)
);