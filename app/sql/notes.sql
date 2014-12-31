BEGIN;
CREATE TABLE notes(
id serial not null primary key,
title varchar not null,
note text not null,
company_id int references company(id) on update cascade on delete cascade,
person_id int references person(id) on update cascade on delete cascade,
opportunity_id int references opportunities(id) on update cascade on delete cascade,
activity_id int references activities(id) on update cascade on delete cascade,
project_id int references projects(id) on update cascade on delete cascade,
ticket_id int references tickets(id) on update cascade on delete cascade,
owner varchar not null references users(username),
alteredby varchar not null references users(username),
created timestamp not null default now(),
lastupdated timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


INSERT INTO notes (title,note,company_id,owner,alteredby,created,lastupdated,usercompanyid)
	(SELECT title,note,company_id,owner,alteredby,created,lastupdated,usercompanyid FROM company_notes);

INSERT INTO notes (title,note,person_id,owner,alteredby,created,lastupdated,usercompanyid)
	(SELECT title,note,person_id,owner,alteredby,created,lastupdated,usercompanyid FROM person_notes);

INSERT INTO notes (title,note,opportunity_id,owner,alteredby,created,lastupdated,usercompanyid)
	(SELECT title,note,opportunity_id,owner,alteredby,created,lastupdated,usercompanyid FROM opportunity_notes);

INSERT INTO notes (title,note,activity_id,owner,alteredby,created,lastupdated,usercompanyid)
	(SELECT title,note,activity_id,owner,alteredby,created,lastupdated,usercompanyid FROM activity_notes);
	
UPDATE notes SET company_id=(SELECT company_id FROM person WHERE id=person_id) WHERE company_id IS NULL;
UPDATE notes SET company_id=(SELECT company_id FROM opportunities WHERE id=opportunity_id) WHERE company_id IS NULL;
UPDATE notes SET person_id=(SELECT person_id FROM opportunities WHERE id=opportunity_id) WHERE person_id IS NULL;
UPDATE notes SET company_id=(SELECT company_id FROM activities WHERE id=activity_id) WHERE company_id IS NULL; 
UPDATE notes SET person_id=(SELECT person_id FROM activities WHERE id=activity_id) WHERE person_id IS NULL;
UPDATE notes SET opportunity_id=(SELECT opportunity_id FROM activities WHERE id=activity_id) WHERE opportunity_id IS NULL;

COMMIT;