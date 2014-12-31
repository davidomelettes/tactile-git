BEGIN;
CREATE TABLE hour_type_groups(
id serial primary key,
name varchar not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
INSERT INTO hour_type_groups(name,usercompanyid) VALUES ('Misc',1);
CREATE TABLE hour_types (
id serial primary key,
name varchar not null,
group_id int not null references hour_type_groups(id) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
INSERT INTO hour_types (name,group_id,usercompanyid) VALUES ('Misc',1,1);
ALTER TABLE hours ADD type_id int references hour_types(id);
UPDATE hours SET type_id=1;
ALTER TABLE hours ALTER type_id SET NOT NULL;
COMMIT;