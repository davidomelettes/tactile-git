BEGIN;
CREATE TABLE news_items (
    id bigserial NOT NULL PRIMARY KEY,
    headline character varying NOT NULL,
    teaser character varying,
    body character varying,
    website character varying,
    publishon date not null default now(),
    withdrawon date,
    visible boolean DEFAULT true NOT NULL,
    website_id bigint NOT NULL references websites(id) on update cascade on delete cascade,
    file_id bigint references file(id) on update cascade on delete cascade,
    lastupdated timestamp without time zone,
    created timestamp without time zone DEFAULT now() NOT NULL,
    alteredby character varying references users(username) ON update cascade on delete set null,
    frontpage boolean DEFAULT false NOT NULL,
	usercompanyid bigint NOT NULL REFERENCES company(id) On update cascade on delete cascade,
    category_id int references webpage_categories(id) on update cascade on delete cascade
);
COMMIT;

