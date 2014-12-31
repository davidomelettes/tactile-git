BEGIN;
CREATE TABLE s3_files (
id serial not null primary key,
bucket varchar not null,
object varchar not null,
filename varchar not null,
content_type varchar not null,
size int not null,
extension varchar,
company_id int references company(id) on update cascade on delete cascade,
person_id int references person(id) on update cascade on delete cascade,
opportunity_id int references opportunities(id) on update cascade on delete cascade,
activity_id int references tactile_activities(id) on update cascade on delete cascade,
created timestamp not null default now(),
owner varchar not null references users(username),
usercompanyid bigint not null references company(id)
);
ALTER TABLE s3_files ADD comment VARCHAR;
COMMIT;