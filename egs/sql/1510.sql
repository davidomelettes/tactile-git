BEGIN;

CREATE TABLE calendar_events (
    id bigserial NOT NULL PRIMARY KEY,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone NOT NULL,
    all_day boolean DEFAULT false NOT NULL,
    summary character varying,
    description character varying,
    location character varying,
    url character varying,
    status character varying,
    owner character varying NOT NULL,
    private boolean DEFAULT true NOT NULL,
    usercompanyid bigint
);

ALTER TABLE ONLY calendar_events
    ADD CONSTRAINT calendar_events_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE ONLY calendar_events
    ADD CONSTRAINT calendar_events_username_fkey FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON 
DELETE CASCADE;
COMMIT;
