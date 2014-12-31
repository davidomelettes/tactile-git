BEGIN;

CREATE TABLE newsletters (
id serial primary key,
name varchar not null,
newsletter_url varchar not null,
send_at timestamp not null default now(),
created timestamp not null default now(),
campaign_id int references campaigns(id) on update cascade on delete cascade,
owner varchar not null references users(username) on update cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE newsletter_recipients (
id serial primary key,
person_id bigint not null references person(id) on update cascade on delete cascade,
sent boolean not null default false,
sent_at timestamp,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE newsletter_views (
id serial primary key,
person_id bigint not null references person(id) on update cascade on delete cascade,
newsletter_id int not null references newsletters(id) on update cascade on delete cascade,
time_viewed timestamp not null default now(),
ip_address inet not null,
user_agent varchar,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE newsletter_urls (
id serial primary key,
url varchar not null,
newsletter_id int not null references newsletters(id),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE newsletter_url_clicks (
id serial primary key,
url_id int not null references newsletter_urls on update cascade on delete cascade,
person_id bigint not null references person(id) on update cascade on delete cascade,
clicked_at timestamp not null default now(),
ip_address inet not null,
user_agent varchar,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE VIEW newsletteroverview AS
SELECT n.*,
c.name AS campaign
FROM newsletters n
LEFT JOIN campaigns c ON (n.campaign_id=c.id);

CREATE VIEW newsletter_viewsoverview AS
SELECT v.*, n.name AS newsletter, p.firstname || ' ' || p.surname AS person
FROM newsletter_views v JOIN newsletters n ON (v.newsletter_id=n.id) JOIN person p ON (v.person_id=p.id);


CREATE VIEW newsletter_url_clicksoverview AS
SELECT c.*, 
u.url AS url, u.newsletter_id,
n.name AS newsletter, p.firstname || ' ' || p.surname AS person
FROM newsletter_url_clicks c JOIN newsletter_urls u ON (c.url_id=u.id) JOIN newsletters n ON (u.newsletter_id=n.id) JOIN person p ON (c.person_id=p.id);

COMMIT;
