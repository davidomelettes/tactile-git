BEGIN;
CREATE TABLE intranet_page_revisions (
    id bigserial NOT NULL PRIMARY KEY,
    content text NOT NULL,
    title character varying NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    intranetpage_id bigint NOT NULL REFERENCES intranet_pages(id) ON UPDATE CASCADE ON DELETE CASCADE,
    revision bigserial NOT NULL
);
ALTER TABLE intranet_pages DROP COLUMN title;
COMMIT;
