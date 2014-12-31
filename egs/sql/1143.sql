BEGIN;
CREATE TABLE hours (
id bigserial primary key,
start_time timestamp not null default now(),
end_time timestamp,
duration interval not null default '0 hours',
description text,
owner varchar not null references users(username) on update cascade on delete cascade,
project_id bigint references projects(id) on update cascade on delete cascade,
task_id bigint references tasks(id) on update cascade on delete cascade,
ticket_id bigint references tickets(id) on update cascade on delete cascade,
opportunity_id bigint references opportunities(id) on update cascade on delete cascade,
billable boolean not null default false,
invoiced boolean not null default false,
overtime boolean not null default false,
created timestamp not null default now(),
lastupdated timestamp not null default now(),
usercompanyid bigserial not null references company(id) on update cascade on delete cascade,
CHECK (start_time < end_time)
);
COMMIT;