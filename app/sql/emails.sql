DROP TABLE emails;
CREATE TABLE emails(
id serial not null primary key,
person_id int references person(id) on update cascade on delete set null,
company_id int references company(id) on update cascade on delete cascade,
email_from varchar not null,
email_to varchar not null,
subject varchar,
body text,
received timestamp not null default now(),
created timestamp not null default now(),
owner varchar not null references users(username) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

ALTER TABLE s3_files ADD COLUMN email_id integer;

DROP VIEW useroverview;
CREATE VIEW useroverview AS SELECT u.username, u."password", u.enabled, u.lastcompanylogin, u.person_id, uca.company_id AS usercompanyid, (p.firstname::text || ' '::text) || p.surname::text AS person, u.dropboxkey
   FROM users u
   LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
   LEFT JOIN person p ON u.person_id = p.id;
