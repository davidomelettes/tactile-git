BEGIN;

CREATE TABLE intranet_sections (
id serial primary key,
title varchar not null,
parent_id int references intranet_sections(id) on update cascade on delete cascade,
position int not null default 0,
owner varchar not null references users(username) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE intranet_section_access (
id serial primary key,
role_id int not null references roles(id) on update cascade on delete cascade,
section_id int not null references intranet_sections(id) on update cascade on delete cascade,
read boolean not null default true,
add_pages boolean not null default false
);

CREATE TABLE intranet_layouts (
id serial primary key,
name varchar not null,
layout text not null,
created timestamp not null default now(),
lastupdated timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE intranet_page_types (
id serial primary key,
name varchar not null,
static boolean not null default true,
layout_id int not null references intranet_layouts on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE VIEW intranet_sectionsoverview AS
SELECT s.* ,
p.title AS parent
FROM intranet_sections s LEFT JOIN intranet_sections p ON (s.parent_id=p.id);

CREATE VIEW intranet_page_typesoverview AS 
SELECT pt.*, l.name AS layout
FROM intranet_page_types pt join intranet_layouts l ON (pt.layout_id=l.id);

CREATE TABLE intranet_config (
id serial primary key,
title varchar not null,
layout_id int not null references intranet_layouts(id) on update cascade,
default_section_id int not null references intranet_sections(id) on update cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE intranet_pages (
id serial primary key,
title varchar not null,
name varchar not null,
type_id int not null references intranet_page_types(id) on update cascade on delete cascade,
section_id int not null references intranet_sections(id) on update cascade on delete cascade,
parent_id int references intranet_pages(id) on update cascade on delete cascade,
position int not null default 0,
owner varchar not null references users(username) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

COMMIT;