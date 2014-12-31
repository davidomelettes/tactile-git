begin;
create table module_admins (
role_id bigint references roles(id) on update cascade on delete cascade,
module_name varchar not null
);
commit;
