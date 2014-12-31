BEGIN;

INSERT INTO permissions (permission, type, title, display) VALUES ('hr-expenses', 'c', 'Expenses', 't');

CREATE TABLE expenses (
  id bigserial PRIMARY KEY,
  expense_ref bigint not null,
  summary varchar not null,
  description varchar,
  amount numeric not null,
  employee_id bigint REFERENCES employees(id) not null,
  usercompanyid bigint REFERENCES company(id) not null,
  project_id bigint REFERENCES projects(id),
  opportunity_id bigint REFERENCES opportunities(id),
  external_ref varchar,
  created timestamp not null default now(),
  lastupdated timestamp not null default now()
);

CREATE VIEW expenses_overview AS SELECT * FROM expenses;

ALTER TABLE expenses ALTER COLUMN amount SET DEFAULT 0;

DROP VIEW expenses_overview;
CREATE VIEW expenses_overview AS SELECT e.id, e.expense_ref, e.summary, e.description, e.amount, e.employee_id, p.firstname || ' ' || p.surname AS employee, e.usercompanyid, e.project_id, pr.name AS project, e.opportunity_id, o.name AS opportunity, e.external_ref, e.created, e.lastupdated FROM expenses e LEFT JOIN employees em ON (e.employee_id = em.id) LEFT JOIN person p ON (em.person_id = p.id) LEFT JOIN projects pr ON (e.project_id = pr.id) LEFT JOIN opportunities o ON (e.opportunity_id = o.id);

COMMIT;