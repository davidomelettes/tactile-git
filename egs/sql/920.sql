--not compatible with 921.sql
BEGIN;

CREATE TABLE ticket_queues (
    id bigserial NOT NULL PRIMARY KEY,
    name character varying NOT NULL UNIQUE,
    parent_queue_id bigint REFERENCES ticket_queues(id),
    usercompanyid bigint NOT NULL REFERENCES company(id),
    keywords character varying,
    default_queue boolean DEFAULT false,
    email_address character varying
);

CREATE TABLE ticket_priorities (
    id bigserial PRIMARY KEY,
    usercompanyid bigint NOT NULL REFERENCES company(id),
    name character varying NOT NULL
);


CREATE TABLE ticket_categories (
    id bigserial NOT NULL PRIMARY KEY,
    usercompanyid bigint NOT NULL REFERENCES company(id),
    name character varying NOT NULL,
UNIQUE (usercompanyid, name)
);

CREATE TABLE ticket_severities (
    id bigserial PRIMARY KEY,
    usercompanyid bigint NOT NULL REFERENCES company(id),
    "index" integer NOT NULL,
    name character varying NOT NULL,
UNIQUE (usercompanyid, "index", name)
);


CREATE TABLE ticket_statuses (
    id bigserial PRIMARY KEY,
    usercompanyid bigint NOT NULL REFERENCES company(id),
    name character varying NOT NULL,
    status_code character varying(4) NOT NULL,
    "index" integer
);
CREATE TABLE tickets (
    id bigserial PRIMARY KEY,
    summary character varying NOT NULL,
    client_ticket_priority_id bigint NOT NULL REFERENCES ticket_priorities(id),
    client_ticket_severity_id bigint NOT NULL REFERENCES ticket_severities(id),
    ticket_queue_id bigint NOT NULL REFERENCES ticket_queues(id),
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    ticket_category_id bigint NOT NULL REFERENCES ticket_categories(id),
    originator_person_id bigint REFERENCES person(id),
    originator_company_id bigint REFERENCES company(id),
    internal_ticket_priority_id bigint NOT NULL REFERENCES ticket_priorities(id),
    internal_ticket_severity_id bigint NOT NULL REFERENCES ticket_severities(id),
    internal_ticket_status_id bigint NOT NULL REFERENCES ticket_statuses(id),
    client_ticket_status_id bigint NOT NULL REFERENCES ticket_statuses(id)
);

CREATE VIEW tickets_overview AS SELECT t.id, t.summary, tpc.name AS client_ticket_priority_id, tpi.name AS internal_ticket_priority_id, tsc.name AS client_ticket_severity_id, tsi.name AS internal_ticket_severity_id, tstc.name AS client_ticket_status_id, tsti.name AS internal_ticket_status_id, tc.name AS ticket_category_id, tq.name AS ticket_queue_id, (((((((COALESCE(p.title, ''::character varying)::text || ' '::text) || p.firstname::text) || ' '::text) || COALESCE(p.middlename, ''::character varying)::text) || ' '::text) || p.surname::text) || ' '::text) || COALESCE(p.suffix, ''::character varying)::text AS originator_person_id, c.name AS originator_company_id, to_char(t.created, 'IYYY-MM-DD HH:MI:SS'::text) AS created, to_char(t.lastupdated, 'IYYY-MM-DD HH:MI:SS'::text) AS lastupdated
   FROM tickets t
   LEFT JOIN ticket_priorities tpc ON t.client_ticket_priority_id = tpc.id
   LEFT JOIN ticket_severities tsc ON t.client_ticket_severity_id = tsc.id
   LEFT JOIN ticket_priorities tpi ON t.internal_ticket_priority_id = tpi.id
   LEFT JOIN ticket_severities tsi ON t.internal_ticket_severity_id = tsi.id
   LEFT JOIN ticket_statuses tstc ON t.client_ticket_status_id = tstc.id
   LEFT JOIN ticket_statuses tsti ON t.internal_ticket_status_id = tsti.id
   LEFT JOIN ticket_categories tc ON t.ticket_category_id = tc.id
   LEFT JOIN ticket_queues tq ON t.ticket_queue_id = tq.id
   LEFT JOIN person p ON t.originator_person_id = p.id
   LEFT JOIN company c ON t.originator_company_id = c.id;



CREATE TABLE ticket_responses (
    id bigserial NOT NULL PRIMARY KEY,
    "type" character varying,
    ticket_id bigint NOT NULL REFERENCES tickets(id),
    log_message character varying,
    "owner" character varying NOT NULL,
    email_address character varying NOT NULL,
    body character varying NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL
);


CREATE TABLE ticket_attachments (
    id bigserial NOT NULL PRIMARY KEY,
    ticket_id bigint NOT NULL REFERENCES tickets(id),
    file_id bigint NOT NULL,
    "type" character varying DEFAULT 'dummy'::character varying,
    size character varying DEFAULT 'dummy'::character varying
);
COMMIT;


