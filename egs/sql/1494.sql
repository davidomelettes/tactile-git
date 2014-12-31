BEGIN;
CREATE TABLE project_issue_statuses(
id serial primary key,
name varchar not null,
closed boolean not null default false,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
ALTER TABLE project_issue_statuses ADD default_value boolean not null default false;

CREATE TABLE project_issues (
id serial primary key,
problem_location text not null,
problem_description text not null,
project_id int not null references projects(id) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now(),
time_fixed timestamp,
status_id int not null references project_issue_statuses(id) on update cascade on delete cascade,
owner varchar not null references users(username) on update cascade on delete cascade,
alteredby varchar not null references users(username) on update cascade on delete cascade,
assigned_to varchar references users(username) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
CREATE VIEW project_issuesoverview AS
SELECT pi.*, p.name AS project, ps.name AS status, ps.closed AS closed
FROM project_issues pi LEFT JOIN projects p ON (pi.project_id=p.id) LEFT JOIN project_issue_statuses ps ON (pi.status_id=ps.id);

COMMIT;