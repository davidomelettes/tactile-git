BEGIN;
CREATE TABLE ticket_slas (
	id bigserial,
	company_id bigint not null,
	response_time bigint not null,
	
	PRIMARY KEY (id),
	FOREIGN KEY (company_id) REFERENCES company(id)
);

CREATE TABLE sla_events (
	id bigserial,
	event varchar(4) NOT NULL,
	ticket_id bigint NOT NULL,
	timestamp timestamp NOT NULL DEFAULT now(),
	
	PRIMARY KEY (id),
	FOREIGN KEY (ticket_id) REFERENCES tickets(id)
);



ALTER TABLE tickets ADD COLUMN ticket_sla_id bigint;
ALTER TABLE tickets ADD CONSTRAINT "ticket_sla_id_fkey" FOREIGN KEY (ticket_sla_id) REFERENCES ticket_slas(id);

ALTER TABLE ticket_slas DROP COLUMN company_id;
ALTER TABLE ticket_slas ADD COLUMN usercompanyid bigint NOT NULL DEFAULT '1';
ALTER TABLE ticket_slas ADD CONSTRAINT "usercompanyid_fkey" FOREIGN KEY (usercompanyid) REFERENCES company(id);

ALTER TABLE ticket_slas ADD COLUMN hours int;

ALTER TABLE ticket_slas ADD COLUMN name varchar NOT NULL;

ALTER TABLE ticket_slas ALTER COLUMN usercompanyid DROP DEFAULT;

CREATE TABLE company_slas (
	id bigserial,
	company_id bigint NOT NULL,
	ticket_sla_id bigint NOT NULL,
	
	FOREIGN KEY (company_id) REFERENCES company(id),
	FOREIGN KEY (ticket_sla_id) REFERENCES ticket_slas(id),
	PRIMARY KEY (id)
);

ALTER TABLE tickets DROP CONSTRAINT ticket_sla_id_fkey;
ALTER TABLE tickets DROP COLUMN ticket_sla_id;
ALTER TABLE tickets ADD COLUMN company_sla_id bigint;
ALTER TABLE tickets ADD CONSTRAINT "tickets_company_sla_id_fkey" FOREIGN KEY (company_sla_id) REFERENCES company_slas(id);

ALTER TABLE tickets ADD COLUMN action_code varchar(4);

COMMIT;