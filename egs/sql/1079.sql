BEGIN;
CREATE TABLE ticket_configurations (
	id bigserial not null,
	usercompanyid bigint not null,
	client_ticket_status_default bigint not null,
	client_ticket_priority_default bigint not null,
	client_ticket_severity_default bigint not null,
	internal_ticket_status_default bigint not null,
	internal_ticket_priority_default bigint not null,
	internal_ticket_severity_default bigint not null,
	ticket_queue_default bigint not null,
	PRIMARY KEY (id),
	FOREIGN KEY (client_ticket_status_default) REFERENCES ticket_statuses(id),
	FOREIGN KEY (client_ticket_priority_default) REFERENCES ticket_priorities(id),
	FOREIGN KEY (client_ticket_severity_default) REFERENCES ticket_severities(id),
	FOREIGN KEY (internal_ticket_status_default) REFERENCES ticket_statuses(id),
	FOREIGN KEY (internal_ticket_priority_default) REFERENCES ticket_priorities(id),
	FOREIGN KEY (internal_ticket_severity_default) REFERENCES ticket_severities(id),
	FOREIGN KEY (ticket_queue_default) REFERENCES ticket_queues(id),
	FOREIGN KEY (usercompanyid) REFERENCES company(id)
);

ALTER TABLE ticket_configurations ADD COLUMN ticket_category_default bigint not null;
COMMIT;