BEGIN;
CREATE TABLE intranet_page_access (
id serial primary key,
intranetpage_id int not null references intranet_pages(id) on update cascade on delete cascade,
role_id int not null references roles(id) on update cascade on delete cascade,
read boolean not null default false,
edit boolean not null default false
);
COMMIT;
