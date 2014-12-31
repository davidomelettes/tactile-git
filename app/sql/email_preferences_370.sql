BEGIN;
CREATE TABLE email_preferences (
id serial not null primary key,
mail_name varchar not null,
send boolean not null default false,
owner varchar not null references users(username) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now()
);
COMMIT;