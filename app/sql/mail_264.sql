CREATE TABLE mail_log (
id serial not null primary key,
name varchar not null,
time_sent timestamp not null default now(),
recipient varchar not null,
comment text
);