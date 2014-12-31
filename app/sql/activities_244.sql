CREATE TABLE tactile_activities (
id serial primary key,
name varchar not null,
description text,
type_id int references activitytype(id) on update cascade on delete set null,
opportunity_id int references opportunities(id) on update cascade on delete cascade,
company_id int references company(id) on update cascade on delete cascade,
person_id int references person(id) on update cascade on delete cascade,
"date" date,
"time" time,
later boolean not null default false,
completed timestamp,
assigned_to varchar not null references users(username) on update cascade on delete cascade,
assigned_by varchar not null references users(username) on update cascade on delete cascade, 
owner varchar not null references users(username) on update cascade on delete cascade,
alteredby varchar not null references users(username) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE VIEW tactile_activities_overview AS
SELECT a.*,
t.name AS type,
o.name AS opportunity,
c.name AS company,
p.firstname || ' ' || p.surname AS person,
CASE WHEN later=true THEN false
WHEN "time" is null then "date"<'today'::date
ELSE (date+time) < now()::timestamp at time zone u.timezone END AS overdue,
CASE WHEN later=true THEN 'infinity'::timestamp
WHEN "time" is null then date+'23:59:59'::time
ELSE date+time END AS due
FROM tactile_activities a
LEFT JOIN activitytype t ON (t.id=a.type_id)
LEFT JOIN opportunities o ON (o.id=a.opportunity_id)
LEFT JOIN company c ON (c.id=a.company_id)
LEFT JOIN person p ON (p.id=a.person_id)
LEFT JOIN users u ON (u.username=a.assigned_to);