BEGIN;

CREATE TABLE faq (
  id bigserial not null,
  name varchar not null,
  usercompanyid bigint references company(id),
  primary key (id)
);

CREATE TABLE faq_section (
  id bigserial not null,
  name varchar not null,
  faq_id bigint references faq(id) not null,
  primary key (id)
);

CREATE TABLE faq_qa (
  id bigserial not null,
  question varchar not null,
  answer varchar not null,
  primary key (id)
);

CREATE TABLE faq_qa_section (
  id bigserial not null,
  faq_qa_id bigint references faq_qa(id),
  faq_section_id bigint references faq_section(id),
  primary key (faq_qa_id, faq_section_id)
);

INSERT INTO permissions (permission, type, title) VALUES ('ticketing-faq', 'c', 'FAQs');
INSERT INTO permissions (permission, type) VALUES ('ticketing-faq-new', 'a');
INSERT INTO permissions (permission, type) VALUES ('ticketing-faq-index', 'a');
INSERT INTO permissions (permission, type) VALUES ('ticketing-faq-view', 'a');
INSERT INTO permissions (permission, type) VALUES ('ticketing-faq-edit', 'a');

ALTER TABLE faq_section ADD COLUMN index bigint not null default 0;

CREATE VIEW faq_overview AS SELECT * FROM faq;
CREATE VIEW faq_section_overview AS SELECT s.id, s.name, s.index, s.faq_id, f.name AS faq FROM faq_section s LEFT JOIN faq f ON (s.faq_id = f.id);
CREATE VIEW faq_qa_overview AS SELECT * FROM faq_qa;

COMMIT;