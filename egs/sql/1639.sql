begin;
create table calendar_shares (
id bigserial primary key,
owner varchar not null references users(username) on update cascade on delete cascade,
username varchar not null references users(username) on update cascade on delete cascade);
commit;
