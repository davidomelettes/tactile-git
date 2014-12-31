--not compatible with 920.sql

BEGIN;
ALTER TABLE tickets RENAME ticket_priority_id TO client_ticket_priority_id;
ALTER TABLE tickets RENAME ticket_severity_id TO client_ticket_severity_id;

ALTER TABLE tickets ADD COLUMN internal_ticket_priority_id bigint;
ALTER TABLE tickets ADD COLUMN internal_ticket_severity_id bigint;
ALTER TABLE tickets ADD CONSTRAINT tickets_internal_ticket_priority_id_fkey FOREIGN KEY (internal_ticket_priority_id) REFERENCES ticket_priorities(id);
ALTER TABLE tickets ADD CONSTRAINT tickets_internal_ticket_severity_id_fkey FOREIGN KEY (internal_ticket_severity_id) REFERENCES ticket_severities(id);

ALTER TABLE tickets DROP CONSTRAINT tickets_ticket_priority_id_fkey;
ALTER TABLE tickets DROP CONSTRAINT tickets_ticket_severity_id_fkey;
ALTER TABLE tickets ADD CONSTRAINT tickets_client_ticket_priority_id_fkey FOREIGN KEY (internal_ticket_priority_id) REFERENCES ticket_priorities(id);
ALTER TABLE tickets ADD CONSTRAINT tickets_client_ticket_severity_id_fkey FOREIGN KEY (internal_ticket_severity_id) REFERENCES ticket_severities(id);

CREATE TABLE ticket_statuses (
    id bigserial,
    usercompanyid bigint not null,
    name varchar not null,
    
    PRIMARY KEY (id),
    FOREIGN KEY (usercompanyid) REFERENCES company(id)
);

ALTER TABLE tickets ADD COLUMN internal_ticket_status_id bigint;
ALTER TABLE tickets ADD COLUMN client_ticket_status_id bigint;
ALTER TABLE tickets ADD CONSTRAINT tickets_internal_ticket_status_id_fkey FOREIGN KEY (internal_ticket_status_id) REFERENCES ticket_statuses(id);
ALTER TABLE tickets ADD CONSTRAINT tickets_client_ticket_status_id_fkey FOREIGN KEY (client_ticket_status_id) REFERENCES ticket_statuses(id);

DROP VIEW tickets_overview;

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
COMMIT;