BEGIN;
--1013.sql will fail, this is ok
CREATE TABLE websites (
    id bigserial primary key,
    name character varying NOT NULL,
    company_id bigint REFERENCES company(id) ON UPDATE CASCADE,
    person_id bigint REFERENCES person(id) ON UPDATE CASCADE,
    news boolean DEFAULT false NOT NULL,
    portfolio boolean DEFAULT false NOT NULL,
    shop boolean DEFAULT false NOT NULL,
    files boolean DEFAULT false NOT NULL,
    access_controlled boolean DEFAULT false NOT NULL,
    usercompanyid bigint NOT NULL REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE (name, usercompanyid)
);

CREATE TABLE webpage_categories (
    id serial PRIMARY KEY,
    name character varying NOT NULL,
    title character varying NOT NULL,
    parent_id bigint REFERENCES webpage_categories(id) ON UPDATE CASCADE ON DELETE CASCADE,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    visible boolean DEFAULT true NOT NULL,
    access_controlled boolean DEFAULT false NOT NULL,
    website_id bigint NOT NULL REFERENCES websites(id) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE (name, website_id, parent_id)
);
CREATE TABLE webpages (
    id bigserial primary key,
    name character varying NOT NULL,
    keywords character varying,
    description character varying,
    visible boolean DEFAULT true NOT NULL,
    page_element boolean DEFAULT false NOT NULL,
    parent_id bigint REFERENCES webpages(id) ON UPDATE CASCADE ON DELETE SET NULL,
    website_id bigint NOT NULL REFERENCES websites(id) ON UPDATE CASCADE ON DELETE CASCADE,
    webpage_category_id integer REFERENCES webpage_categories(id) ON UPDATE CASCADE ON DELETE SET NULL,
    "owner" character varying NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
    alteredby character varying REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    access_controlled boolean DEFAULT false NOT NULL,
    publishon date DEFAULT now() NOT NULL,
    withdrawon date,
    CONSTRAINT webpage_check CHECK (((page_element IS FALSE) OR ((page_element IS TRUE) AND (parent_id IS NOT NULL)))),
UNIQUE (name, website_id, parent_id)
);


CREATE TABLE customers (
    id serial PRIMARY KEY,
    person_id bigint NOT NULL REFERENCES person(id),
    website_id bigint NOT NULL REFERENCES websites(id) ON UPDATE CASCADE ON DELETE CASCADE,
    username character varying,
    "password" character varying,
    confirmation character varying,
    secret_question character varying,
    secret_answer character varying,
    additional text,
    created timestamp without time zone DEFAULT now() NOT NULL,
UNIQUE (website_id, username)
);



COMMIT;
