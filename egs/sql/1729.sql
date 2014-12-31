BEGIN;
CREATE TABLE logged_calls (
    id bigserial NOT NULL PRIMARY KEY,
	subject varchar not null,
    company_id bigint NOT NULL REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE,
    person_id bigint REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE,
	project_id int REFERENCES projects(id) on update CASCADE ON DELETE CASCADE,
	opportunity_id int REFERENCES opportunities(id) on update CASCADE ON DELETE CASCADE,
	activity_id int REFERENCES activities(id) on update CASCADE ON DELETE CASCADE,
	ticket_id int REFERENCES tickets(id) on update CASCADE ON DELETE CASCADE,
    parent_id bigint REFERENCES logged_calls(id) ON UPDATE CASCADE ON DELETE CASCADE,
    direction character varying NOT NULL,
    start_time timestamp without time zone DEFAULT now() NOT NULL,
    end_time timestamp without time zone NOT NULL,
    notes text,
    "owner" character varying NOT NULL REFERENCES users(username),
    usercompanyid bigint NOT NULL REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE VIEW loggedcallsoverview AS
    SELECT lc.*, (lc.end_time - lc.start_time) AS duration, c.name AS company, (((p.firstname)::text || ' '::text) || (p.surname)::text) AS person FROM ((logged_calls lc LEFT JOIN company c ON ((lc.company_id = c.id))) LEFT JOIN person p ON ((lc.person_id = p.id)));
COMMIT;
