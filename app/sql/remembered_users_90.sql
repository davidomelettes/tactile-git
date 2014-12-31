begin;

create table remembered_users(
	id serial not null primary key,
	username varchar not null references users(username) on update cascade on delete cascade,
	hash varchar not null,
	expires timestamp not null
);

commit;