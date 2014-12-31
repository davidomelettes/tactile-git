begin;
create table project_notes(
id serial primary key, title varchar not null,note varchar not null,project_id bigint references projects(id) on update cascade on delete cascade,owner varchar references users(username) on update cascade,created timestamp not null default now(),lastupdated timestamp not null default now(),alteredby varchar references users(username) on update cascade,usercompanyid bigint not null references company(id) on update cascade on delete cascade);

CREATE VIEW project_notesoverview AS
 SELECT n.id, n.title, n.note,n."owner", n.alteredby, n.lastupdated, n.created, n.usercompanyid
   FROM project_notes n
   JOIN projects p ON p.id = n.project_id;

create table task_notes(
id serial primary key, title varchar not null,note varchar not null,task_id bigint references tasks(id) on update cascade on delete cascade,owner varchar references users(username) on update cascade,created timestamp not null default now(),lastupdated timestamp not null default now(),alteredby varchar references users(username) on update cascade,usercompanyid bigint not null references company(id) on update cascade on delete cascade);

CREATE VIEW task_notesoverview AS
 SELECT n.id, n.title, n.note,n."owner", n.alteredby, n.lastupdated, n.created, n.usercompanyid
   FROM task_notes n
   JOIN tasks t ON t.id = n.task_id;
commit;
