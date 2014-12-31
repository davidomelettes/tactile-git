BEGIN;
DROP VIEW tickets_overview;
CREATE VIEW tickets_overview AS SELECT t.id, t.summary, (tpc."index"::text || '-'::text) || tpc.name::text AS client_ticket_priority, t.client_ticket_priority_id, (tpi."index"::text || '-'::text) || tpi.name::text AS internal_ticket_priority, t.internal_ticket_priority_id, (tsc."index"::text || '-'::text) || tsc.name::text AS client_ticket_severity, t.client_ticket_severity_id, (tsi."index"::text || '-'::text) || tsi.name::text AS internal_ticket_severity, t.internal_ticket_severity_id, tstc.name AS client_ticket_status, t.client_ticket_status_id, tsti.name AS internal_ticket_status, t.internal_ticket_status_id, tc.name AS ticket_category, t.ticket_category_id, tq.name AS ticket_queue, t.ticket_queue_id, (((((((COALESCE(p.title, ''::character varying)::text || ' '::text) || p.firstname::text) || ' '::text) || COALESCE(p.middlename, ''::character varying)::text) || ' '::text) || p.surname::text) || ' '::text) || COALESCE(p.suffix, ''::character varying)::text AS originator_person, t.originator_person_id, c.name AS originator_company, t.originator_company_id, to_char(t.created, 'IYYY-MM-DD HH24:MI:SS'::text) AS created, to_char(t.lastupdated, 'IYYY-MM-DD HH24:MI:SS'::text) AS lastupdated, tq.usercompanyid
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