BEGIN;
CREATE TABLE ticket_messages (
	id bigserial,
	queue_id bigint not null,
	action_code varchar(4), -- OPEN,CLOS,UPDT,FINL,TIMO
	message varchar,
	
	PRIMARY KEY (id),
	FOREIGN KEY (queue_id) REFERENCES company(id)
);

CREATE TABLE entity_attachments (
	id bigserial,
	
	entity_table varchar not null,
	entity_id bigint not null,
	
	file_id bigint not null,
	
	PRIMARY KEY(id),
	FOREIGN KEY (file_id) REFERENCES file(id)
);

CREATE VIEW entity_attachments_overview AS SELECT ea.id, ea.entity_id, ea.entity_table, f.id as file_id, f.name AS file, f.type, f.size, f.note FROM entity_attachments ea LEFT JOIN file f ON (ea.file_id = f.id);
COMMIT;