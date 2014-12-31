BEGIN;

CREATE TABLE resource_types (
  id bigserial not null PRIMARY KEY,
  name varchar not null,
  usercompanyid bigint not null references company(id)
);

CREATE TABLE resource_templates (
  id bigserial NOT NULL PRIMARY KEY,
  person_id bigint NOT NULL REFERENCES person(id),
  resource_type bigint NOT NULL REFERENCES resource_types(id),
  standard_rate numeric,
  overtime_rate numeric,
  quantity integer,
  cost numeric,
  usercompanyid bigint NOT NULL REFERENCES company(id)
);

ALTER TABLE resources ADD COLUMN resource_type bigint REFERENCES resource_types(id);
ALTER TABLE resources RENAME COLUMN resource_type TO resource_type_id;

COMMIT;