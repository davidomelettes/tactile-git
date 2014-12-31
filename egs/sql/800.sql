BEGIN;
--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: people_in_categories; Type: TABLE; Schema: public; Owner: te; Tablespace: 
--

CREATE TABLE people_in_categories (
    person_id bigint NOT NULL,
    category_id bigint NOT NULL
);


ALTER TABLE public.people_in_categories OWNER TO te;

--
-- Name: categoryfk; Type: FK CONSTRAINT; Schema: public; Owner: te
--

ALTER TABLE ONLY people_in_categories
    ADD CONSTRAINT categoryfk FOREIGN KEY (category_id) REFERENCES contact_categories(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: personfk; Type: FK CONSTRAINT; Schema: public; Owner: te
--

ALTER TABLE ONLY people_in_categories
    ADD CONSTRAINT personfk FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--
COMMIT;
