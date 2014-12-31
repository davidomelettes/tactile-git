BEGIN;
CREATE TABLE store_suppliers (
    id bigserial PRIMARY KEY,
    name varchar NOT NULL,
    description text,
    website varchar,
    image bigint REFERENCES file(id) ON UPDATE CASCADE,
    "owner" character varying NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL,
    alteredby character varying REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    company_id bigint REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE,
    usercompanyid bigint NOT NULL REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE
);
alter table store_products add foreign key (supplier_id) references store_suppliers(id) on update cascade on delete set null;
COMMIT;
