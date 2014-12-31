--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET search_path = public, pg_catalog;


--
-- Name: _id_seq; Type: SEQUENCE; Schema: public; Owner: www-data
--

CREATE SEQUENCE _id_seq
    START WITH 1
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- Name: _id_seq; Type: SEQUENCE SET; Schema: public; Owner: www-data
--

SELECT pg_catalog.setval('_id_seq', 1, false);


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: account_plans; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE account_plans (
    id serial NOT NULL,
    name character varying NOT NULL,
    user_limit integer DEFAULT 0 NOT NULL,
    file_space integer DEFAULT 0 NOT NULL,
    opportunity_limit integer DEFAULT 0 NOT NULL,
    contact_limit integer DEFAULT 0 NOT NULL,
    cost_per_month numeric DEFAULT 0 NOT NULL
);


--
-- Name: account_plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('account_plans', 'id'), 2, true);


--
-- Name: account_statuses; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE account_statuses (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    disallow_invoices integer DEFAULT 0 NOT NULL
);


--
-- Name: account_statuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('account_statuses', 'id'), 1, false);



--
-- Name: activitytype; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE activitytype (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    "position" integer DEFAULT 0 NOT NULL
);


--
-- Name: company; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE company (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    accountnumber character varying,
    creditlimit integer,
    vatnumber character varying,
    companynumber character varying,
    website character varying,
    employees integer,
    usercompanyid bigint NOT NULL,
    parent_id bigint,
    "owner" character varying NOT NULL,
    assigned character varying,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    alteredby character varying,
    description text,
    is_lead boolean DEFAULT false NOT NULL,
    is_account boolean DEFAULT true NOT NULL,
    CONSTRAINT company_accountdetails CHECK ((((name)::text <> ''::text) AND ((accountnumber)::text <> ''::text))),
    CONSTRAINT company_branchcompanyid CHECK ((id <> parent_id)),
    CONSTRAINT company_check CHECK ((((accountnumber IS NULL) AND is_lead) OR ((accountnumber IS NOT NULL) AND (NOT is_lead))))
);



--
-- Name: opportunities; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE opportunities (
    id bigserial NOT NULL,
    status_id bigint,
    campaign_id bigint,
    company_id bigint,
    person_id bigint,
    "owner" character varying NOT NULL,
    name character varying NOT NULL,
    description character varying,
    cost numeric(10,2) DEFAULT 0.0,
    probability integer DEFAULT 0 NOT NULL,
    enddate date NOT NULL,
    usercompanyid bigint NOT NULL,
    type_id integer,
    source_id bigint,
    nextstep character varying,
    assigned character varying,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    alteredby character varying NOT NULL
);


SET default_with_oids = false;

--
-- Name: person; Type: TABLE; Schema: public; Owner: btf; Tablespace: 
--

CREATE TABLE person (
    id bigserial NOT NULL,
    title character varying,
    firstname character varying NOT NULL,
    middlename character varying,
    surname character varying NOT NULL,
    suffix character varying,
    department character varying,
    jobtitle character varying,
    dob date,
    ni character varying,
    marital smallint,
    lang character(2) NOT NULL,
    company_id bigint,
    "owner" character varying NOT NULL,
    userdetail boolean DEFAULT false NOT NULL,
    reports_to bigint,
    can_call boolean DEFAULT true NOT NULL,
    can_email boolean DEFAULT true NOT NULL,
    assigned_to character varying,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    alteredby character varying,
    usercompanyid bigint NOT NULL,
    crm_source bigint,
    description text
);


SET default_with_oids = false;

--
-- Name: company_classifications; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_classifications (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_classifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_classifications', 'id'), 2, true);


SET default_with_oids = true;

--
-- Name: company_contact_methods; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE company_contact_methods (
    contact character varying NOT NULL,
    "type" character(1) NOT NULL,
    company_id bigint NOT NULL,
    name character varying NOT NULL,
    main boolean DEFAULT false NOT NULL,
    billing boolean DEFAULT false NOT NULL,
    shipping boolean DEFAULT false NOT NULL,
    payment boolean DEFAULT false NOT NULL,
    technical boolean DEFAULT false NOT NULL,
    id bigserial NOT NULL
);


--
-- Name: company_contact_methods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www-data
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_contact_methods', 'id'), 18304, true);


SET default_with_oids = false;

--
-- Name: company_crm; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_crm (
    id serial NOT NULL,
    company_id bigint NOT NULL,
    classification_id integer,
    source_id integer,
    revenue numeric,
    industry_id integer,
    terms integer,
    status_id integer,
    rating_id integer,
    account_status_id integer,
    type_id integer,
    stock_symbol character varying,
    sic_code character varying,
    usercompanyid bigint
);


--
-- Name: company_crm_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_crm', 'id'), 68, true);


--
-- Name: company_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company', 'id'), 18948, true);


--
-- Name: company_industries; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_industries (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_industries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_industries', 'id'), 3, true);


--
-- Name: company_ratings; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_ratings (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_ratings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_ratings', 'id'), 4, true);

--
-- Name: company_sources; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_sources (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_sources_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_sources', 'id'), 5, true);


--
-- Name: company_statuses; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_statuses (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_statuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_statuses', 'id'), 2, true);


--
-- Name: company_types; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE company_types (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: company_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('company_types', 'id'), 5, true);


--
-- Name: companyaddress; Type: TABLE; Schema: public; Owner: btf; Tablespace: 
--

CREATE TABLE companyaddress (
    street1 character varying NOT NULL,
    street2 character varying,
    street3 character varying,
    town character varying NOT NULL,
    county character varying,
    postcode character varying NOT NULL,
    countrycode character(2) NOT NULL,
    company_id bigint NOT NULL,
    name character varying DEFAULT 'MAIN'::character varying NOT NULL,
    main boolean DEFAULT false NOT NULL,
    billing boolean DEFAULT false NOT NULL,
    shipping boolean DEFAULT false NOT NULL,
    payment boolean DEFAULT false NOT NULL,
    technical boolean DEFAULT false NOT NULL,
    id serial NOT NULL
);


--
-- Name: companyaddress_id_seq; Type: SEQUENCE SET; Schema: public; Owner: btf
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('companyaddress', 'id'), 17278, true);


SET default_with_oids = true;

--
-- Name: countries; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE countries (
    code character(2) NOT NULL,
    name character varying NOT NULL
);


--
-- Name: companyaddressoverview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW companyaddressoverview AS
    SELECT ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, ca.company_id, ca.name, ca.main, ca.billing, ca.shipping, ca.payment, ca.technical, ca.id, c.name AS company, (((((((((((((ca.street1)::text || ', '::text) || (COALESCE(ca.street2, ''::character varying))::text) || ', '::text) || (COALESCE(ca.street3, ''::character varying))::text) || ', '::text) || (ca.town)::text) || ', '::text) || (COALESCE(ca.county, ''::character varying))::text) || ', '::text) || (COALESCE(ca.postcode, ''::character varying))::text) || ', '::text) || (co.name)::text) AS address, co.name AS country FROM ((companyaddress ca JOIN company c ON ((c.id = ca.company_id))) JOIN countries co ON ((ca.countrycode = co.code)));


SET default_with_oids = false;

--
-- Name: companyroles; Type: TABLE; Schema: public; Owner: ms; Tablespace: 
--

CREATE TABLE companyroles (
    id bigserial NOT NULL,
    companyid bigint NOT NULL,
    roleid bigint NOT NULL,
    "read" boolean DEFAULT false NOT NULL,
    "write" boolean DEFAULT false NOT NULL
);


--
-- Name: hasrole; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE hasrole (
    roleid bigint NOT NULL,
    username character varying NOT NULL,
    id bigserial NOT NULL
);


--
-- Name: companyoverview; Type: VIEW; Schema: public; Owner: pmk
--

CREATE VIEW companyoverview AS
    SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, CASE WHEN (hr.username IS NULL) THEN c."owner" ELSE hr.username END AS usernameaccess FROM ((((((company c LEFT JOIN companyaddress ca ON (((c.id = ca.company_id) AND ca.main))) LEFT JOIN company_contact_methods p ON ((((c.id = p.company_id) AND p.main) AND (p."type" = 'T'::bpchar)))) LEFT JOIN company_contact_methods f ON ((((c.id = f.company_id) AND f.main) AND (f."type" = 'F'::bpchar)))) LEFT JOIN company_contact_methods e ON ((((c.id = e.company_id) AND e.main) AND (e."type" = 'E'::bpchar)))) LEFT JOIN companyroles cr ON (((c.id = cr.companyid) AND cr."read"))) LEFT JOIN hasrole hr ON ((cr.roleid = hr.roleid))) UNION SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, c."owner" AS usernameaccess FROM ((((company c LEFT JOIN companyaddress ca ON (((c.id = ca.company_id) AND ca.main))) LEFT JOIN company_contact_methods p ON ((((c.id = p.company_id) AND p.main) AND (p."type" = 'T'::bpchar)))) LEFT JOIN company_contact_methods f ON ((((c.id = f.company_id) AND f.main) AND (f."type" = 'F'::bpchar)))) LEFT JOIN company_contact_methods e ON ((((c.id = e.company_id) AND e.main) AND (e."type" = 'E'::bpchar))));


--
-- Name: companyoverview2; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW companyoverview2 AS
    SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, c.is_lead, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, hr.username AS usernameaccess FROM ((((((company c LEFT JOIN companyaddress ca ON (((c.id = ca.company_id) AND ca.main))) LEFT JOIN company_contact_methods p ON ((((c.id = p.company_id) AND p.main) AND (p."type" = 'T'::bpchar)))) LEFT JOIN company_contact_methods f ON ((((c.id = f.company_id) AND f.main) AND (f."type" = 'F'::bpchar)))) LEFT JOIN company_contact_methods e ON ((((c.id = e.company_id) AND e.main) AND (e."type" = 'E'::bpchar)))) LEFT JOIN companyroles cr ON (((c.id = cr.companyid) AND cr."read"))) LEFT JOIN hasrole hr ON ((cr.roleid = hr.roleid)));

--
-- Name: companypermissions; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE companypermissions (
    id bigserial NOT NULL,
    usercompanyid bigint NOT NULL,
    permissionid bigint NOT NULL
);


--
-- Name: companypermissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('companypermissions', 'id'), 13, true);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE permissions (
    id bigserial NOT NULL,
    permission character varying NOT NULL,
    "type" character varying(5),
    description character varying,
    title character varying,
    display boolean DEFAULT true,
    "position" integer
);


--
-- Name: companypermissionsoverview; Type: VIEW; Schema: public; Owner: pb
--

CREATE VIEW companypermissionsoverview AS
    SELECT companypermissions.permissionid, companypermissions.usercompanyid, permissions.permission FROM (companypermissions JOIN permissions ON ((companypermissions.permissionid = permissions.id)));


--
-- Name: companyroles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ms
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('companyroles', 'id'), 11294, true);


--
-- Name: companyrolesoverview; Type: VIEW; Schema: public; Owner: ms
--

CREATE VIEW companyrolesoverview AS
    SELECT cr.companyid, cr.roleid, cr."read", cr."write", hr.username FROM (companyroles cr LEFT JOIN hasrole hr ON ((cr.roleid = hr.roleid)));


SET default_with_oids = true;


--
-- Name: email_preferences; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE email_preferences (
    id serial NOT NULL,
    mail_name character varying NOT NULL,
    send boolean DEFAULT false NOT NULL,
    "owner" character varying NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: email_preferences_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('email_preferences', 'id'), 4, true);

--
-- Name: file; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE file (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    "type" character varying,
    size bigint,
    created timestamp without time zone DEFAULT now() NOT NULL,
    revision bigint,
    note character varying,
    usercompanyid bigint NOT NULL,
    file oid
);

--
-- Name: haspermission; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE haspermission (
    roleid bigint NOT NULL,
    permissionsid bigint NOT NULL,
    id bigserial NOT NULL
);


--
-- Name: haspermission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('haspermission', 'id'), 253, true);


--
-- Name: hasrole_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('hasrole', 'id'), 227, true);


SET default_with_oids = true;

--
-- Name: users; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE users (
    username character varying NOT NULL,
    "password" character(32) NOT NULL,
    lastcompanylogin bigint,
    person_id bigint NOT NULL,
    last_login timestamp without time zone,
    dropboxkey character varying(12),
    is_admin boolean DEFAULT false NOT NULL,
    enabled boolean DEFAULT true NOT NULL,
    timezone character varying DEFAULT 'Europe/London'::character varying NOT NULL,
    terms_agreed timestamp without time zone
);


--
-- Name: hasrolesoverview; Type: VIEW; Schema: public; Owner: pb
--

CREATE VIEW hasrolesoverview AS
    SELECT users.username, users."password", users.lastcompanylogin, users.person_id FROM (hasrole JOIN users ON (((users.username)::text = (hasrole.username)::text)));


SET default_with_oids = true;

--
-- Name: lang; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE lang (
    code character(2) NOT NULL,
    name character varying NOT NULL
);


SET default_with_oids = false;

--
-- Name: mail_log; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE mail_log (
    id serial NOT NULL,
    name character varying NOT NULL,
    time_sent timestamp without time zone DEFAULT now() NOT NULL,
    time_received timestamp without time zone,
    recipient character varying NOT NULL,
    product varchar DEFAULT 'tactile',
    image varchar DEFAULT 'email_logo.png',
    username varchar,
    "comment" text,
	token varchar
);


--
-- Name: notes; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE notes (
    id serial NOT NULL,
    title character varying NOT NULL,
    note text NOT NULL,
    company_id integer,
    person_id integer,
    opportunity_id integer,
    activity_id integer,
    project_id integer,
    ticket_id integer,
    "owner" character varying NOT NULL,
    alteredby character varying NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    usercompanyid bigint NOT NULL,
    private boolean DEFAULT false NOT NULL,
    deleted boolean DEFAULT false NOT NULL
);


--
-- Name: notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('notes', 'id'), 155, true);



--
-- Name: user_company_access; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE user_company_access (
    username character varying NOT NULL,
    company_id bigint NOT NULL,
    id bigserial NOT NULL,
    enabled boolean DEFAULT true NOT NULL
);


--
-- Name: omelette_useroverview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW omelette_useroverview AS
    SELECT u.username, u."password", u.lastcompanylogin, u.person_id, u.last_login, u.dropboxkey, u.is_admin, u.enabled, uca.company_id AS usercompanyid, (((p.firstname)::text || ' '::text) || (p.surname)::text) AS person FROM ((users u LEFT JOIN user_company_access uca ON (((u.username)::text = (uca.username)::text))) LEFT JOIN person p ON ((u.person_id = p.id)));


--
-- Name: opportunities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www-data
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('opportunities', 'id'), 92, true);


SET default_with_oids = true;

--
-- Name: opportunitysource; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE opportunitysource (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    "position" integer DEFAULT 0 NOT NULL
);


--
-- Name: opportunitystatus; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE opportunitystatus (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    description character varying,
    open boolean DEFAULT true NOT NULL,
    "position" integer DEFAULT 0 NOT NULL,
    won boolean DEFAULT false NOT NULL
);


SET default_with_oids = false;

--
-- Name: opportunitytype; Type: TABLE; Schema: public; Owner: te; Tablespace: 
--

CREATE TABLE opportunitytype (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    "position" integer DEFAULT 0 NOT NULL
);


--
-- Name: campaigns; Type: TABLE; Schema: public; Owner: www-data; Tablespace: 
--

CREATE TABLE campaigns (
    id bigserial NOT NULL,
    campaign_type_id bigint NOT NULL,
    campaign_status_id bigint NOT NULL,
    name character varying NOT NULL,
    description text,
    startdate date,
    enddate date,
    actual_cost numeric(10,2) DEFAULT 0.00,
    active boolean DEFAULT true NOT NULL,
    number_sent integer,
    budget numeric,
    expected_cost numeric(10,2),
    objective text,
    expected_revenue numeric,
    actual_revenue numeric,
    expected_response integer,
    actual_response integer,
    target_audience character varying,
    usercompanyid bigint NOT NULL
);

--
-- Name: opportunitiesoverview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW opportunitiesoverview AS
    SELECT o.id, o.status_id, o.campaign_id, o.company_id, o.person_id, o."owner", o.name, o.description, o.cost, o.probability, o.enddate, o.usercompanyid, o.type_id, o.source_id, o.nextstep, o.assigned, o.created, o.lastupdated, o.alteredby, c.name AS company, (((p.firstname)::text || ' '::text) || (p.surname)::text) AS person, cam.name AS campaign, os.name AS source, ot.name AS "type", opportunitystatus.name AS status, CASE WHEN (opportunitystatus.* IS NULL) THEN false ELSE opportunitystatus.open END AS open, CASE WHEN (opportunitystatus.* IS NULL) THEN false ELSE opportunitystatus.won END AS won FROM ((((((opportunities o LEFT JOIN company c ON ((o.company_id = c.id))) LEFT JOIN person p ON ((o.person_id = p.id))) LEFT JOIN campaigns cam ON ((o.campaign_id = cam.id))) LEFT JOIN opportunitysource os ON ((o.source_id = os.id))) LEFT JOIN opportunitytype ot ON ((o.type_id = ot.id))) LEFT JOIN opportunitystatus ON ((o.status_id = opportunitystatus.id)));


--
-- Name: opportunity_notes; Type: TABLE; Schema: public; Owner: te; Tablespace: 
--

CREATE TABLE opportunity_notes (
    id bigserial NOT NULL,
    title character varying NOT NULL,
    note text NOT NULL,
    opportunity_id bigint NOT NULL,
    "owner" character varying NOT NULL,
    alteredby character varying NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: opportunity_notes_id_seq; Type: SEQUENCE SET; Schema: public; Owner: te
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('opportunity_notes', 'id'), 6, true);


--
-- Name: opportunitysource_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www-data
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('opportunitysource', 'id'), 2, true);


--
-- Name: opportunitystatus_id_seq; Type: SEQUENCE SET; Schema: public; Owner: www-data
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('opportunitystatus', 'id'), 6, true);


--
-- Name: opportunitytype_id_seq; Type: SEQUENCE SET; Schema: public; Owner: te
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('opportunitytype', 'id'), 2, true);

SET default_with_oids = false;

--
-- Name: payment_records; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE payment_records (
    id serial NOT NULL,
    account_id integer NOT NULL,
    amount numeric NOT NULL,
    pre_authed boolean DEFAULT false NOT NULL,
    auth_code character varying,
    test_status character varying,
    card_no character varying,
    card_expiry character varying,
    cardholder_name character varying,
    created timestamp without time zone DEFAULT now() NOT NULL,
    authorised boolean DEFAULT false NOT NULL,
    trans_id character varying,
    "type" character varying NOT NULL,
    payment_id integer
);


--
-- Name: payment_records_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('payment_records', 'id'), 61, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('permissions', 'id'), 107, true);


--
-- Name: person_contact_methods; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE person_contact_methods (
    id serial NOT NULL,
    contact character varying NOT NULL,
    "type" character(1) NOT NULL,
    person_id bigint NOT NULL,
    name character varying NOT NULL,
    main boolean DEFAULT false NOT NULL,
    billing boolean DEFAULT false NOT NULL,
    shipping boolean DEFAULT false NOT NULL,
    payment boolean DEFAULT false NOT NULL,
    technical boolean DEFAULT false NOT NULL
);


--
-- Name: person_contact_methods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('person_contact_methods', 'id'), 16593, true);


--
-- Name: person_id_seq; Type: SEQUENCE SET; Schema: public; Owner: btf
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('person', 'id'), 33853, true);



--
-- Name: personaddress; Type: TABLE; Schema: public; Owner: btf; Tablespace: 
--

CREATE TABLE personaddress (
    street1 character varying NOT NULL,
    street2 character varying,
    street3 character varying,
    town character varying NOT NULL,
    county character varying,
    postcode character varying NOT NULL,
    countrycode character(2) NOT NULL,
    person_id bigint NOT NULL,
    name character varying DEFAULT 'MAIN'::character varying NOT NULL,
    main boolean DEFAULT false NOT NULL,
    billing boolean DEFAULT false NOT NULL,
    shipping boolean DEFAULT false NOT NULL,
    payment boolean DEFAULT false NOT NULL,
    technical boolean DEFAULT false NOT NULL,
    id serial NOT NULL
);


--
-- Name: personaddress_id_seq; Type: SEQUENCE SET; Schema: public; Owner: btf
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('personaddress', 'id'), 8, true);


--
-- Name: personaddress_overview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW personaddress_overview AS
    SELECT personaddress.id, (((((((((((personaddress.street1)::text || ', '::text) || (personaddress.street2)::text) || ', '::text) || (personaddress.street3)::text) || ', '::text) || (personaddress.town)::text) || ', '::text) || (personaddress.county)::text) || ', '::text) || (personaddress.postcode)::text) AS address, personaddress.countrycode, personaddress.person_id, personaddress.name, personaddress.main, personaddress.billing, personaddress.shipping, personaddress.payment, personaddress.technical FROM personaddress;


--
-- Name: personoverview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW personoverview AS
    (SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, c.name AS company, (((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) || (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text) || (COALESCE(p.suffix, ''::character varying))::text) AS fullname, ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, CASE WHEN (hr.username IS NULL) THEN p."owner" ELSE hr.username END AS usernameaccess FROM (((((((person p LEFT JOIN company c ON ((c.id = p.company_id))) LEFT JOIN person_contact_methods ph ON ((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) LEFT JOIN person_contact_methods fa ON ((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) LEFT JOIN person_contact_methods mo ON ((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) LEFT JOIN person_contact_methods e ON ((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar)))) LEFT JOIN companyroles cr ON (((cr.companyid = p.company_id) AND cr."read"))) LEFT JOIN hasrole hr ON ((hr.roleid = cr.roleid))) UNION SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, c.name AS company, (((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) || (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text) || (COALESCE(p.suffix, ''::character varying))::text) AS fullname, ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, p."owner" AS usernameaccess FROM (((((person p LEFT JOIN company c ON ((c.id = p.company_id))) LEFT JOIN person_contact_methods ph ON ((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) LEFT JOIN person_contact_methods fa ON ((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) LEFT JOIN person_contact_methods mo ON ((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) LEFT JOIN person_contact_methods e ON ((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar))))) UNION SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, c.name AS company, (((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) || (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text) || (COALESCE(p.suffix, ''::character varying))::text) AS fullname, ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, c."owner" AS usernameaccess FROM (((((person p LEFT JOIN company c ON ((c.id = p.company_id))) LEFT JOIN person_contact_methods ph ON ((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) LEFT JOIN person_contact_methods fa ON ((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) LEFT JOIN person_contact_methods mo ON ((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) LEFT JOIN person_contact_methods e ON ((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar))));


--
-- Name: personoverview2; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW personoverview2 AS
    SELECT p.id, p.title, p.firstname, p.middlename, p.surname, p.suffix, p.department, p.jobtitle, p.dob, p.ni, p.marital, p.lang, p.company_id, p."owner", p.userdetail, p.reports_to, p.can_call, p.can_email, p.assigned_to, p.created, p.lastupdated, p.alteredby, p.usercompanyid, c.name AS company, (((((((((COALESCE(p.title, ''::character varying))::text || ' '::text) || (p.firstname)::text) || ' '::text) || (COALESCE(p.middlename, ''::character varying))::text) || ' '::text) || (p.surname)::text) || ' '::text) || (COALESCE(p.suffix, ''::character varying))::text) AS fullname, ph.contact AS phone, fa.contact AS fax, mo.contact AS mobile, e.contact AS email, hr.username AS usernameaccess FROM (((((((person p LEFT JOIN company c ON ((c.id = p.company_id))) LEFT JOIN person_contact_methods ph ON ((((p.id = ph.person_id) AND ph.main) AND (ph."type" = 'T'::bpchar)))) LEFT JOIN person_contact_methods fa ON ((((p.id = fa.person_id) AND fa.main) AND (fa."type" = 'F'::bpchar)))) LEFT JOIN person_contact_methods mo ON ((((p.id = mo.person_id) AND mo.main) AND (mo."type" = 'M'::bpchar)))) LEFT JOIN person_contact_methods e ON ((((p.id = e.person_id) AND e.main) AND (e."type" = 'E'::bpchar)))) LEFT JOIN companyroles cr ON (((cr.companyid = p.company_id) AND cr."read"))) LEFT JOIN hasrole hr ON ((hr.roleid = cr.roleid)));


--
-- Name: recently_viewed; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE recently_viewed (
    id serial NOT NULL,
    "owner" character varying NOT NULL,
    label character varying NOT NULL,
    "type" character varying NOT NULL,
    link_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL
);


--
-- Name: recently_viewed_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('recently_viewed', 'id'), 69, true);


--
-- Name: remembered_users; Type: TABLE; Schema: public; Owner: pmk; Tablespace: 
--

CREATE TABLE remembered_users (
    id serial NOT NULL,
    username character varying NOT NULL,
    hash character varying NOT NULL,
    expires timestamp without time zone NOT NULL
);


--
-- Name: remembered_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pmk
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('remembered_users', 'id'), 72, true);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: pb; Tablespace: 
--

CREATE TABLE roles (
    id bigserial NOT NULL,
    description text,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: pb
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('roles', 'id'), 63, true);


--
-- Name: s3_files; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE s3_files (
    id serial NOT NULL,
    bucket character varying NOT NULL,
    "object" character varying NOT NULL,
    filename character varying NOT NULL,
    content_type character varying NOT NULL,
    size integer NOT NULL,
    extension character varying,
    company_id integer,
    person_id integer,
    opportunity_id integer,
    activity_id integer,
    created timestamp without time zone DEFAULT now() NOT NULL,
    "owner" character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    "comment" character varying
);


--
-- Name: s3_files_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('s3_files', 'id'), 16, true);



--
-- Name: system_companies; Type: TABLE; Schema: public; Owner: te; Tablespace: 
--

CREATE TABLE system_companies (
    id bigserial NOT NULL,
    company_id bigint NOT NULL,
    enabled boolean DEFAULT true NOT NULL,
    theme character varying DEFAULT 'default'::character varying NOT NULL
);


--
-- Name: system_companies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: te
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('system_companies', 'id'), 4, true);


--
-- Name: system_companiesoverview; Type: VIEW; Schema: public; Owner: te
--

CREATE VIEW system_companiesoverview AS
    SELECT sc.id, sc.company_id, sc.enabled, c.name AS company FROM (system_companies sc LEFT JOIN company c ON ((sc.company_id = c.id)));


--
-- Name: tactile_accounts; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE tactile_accounts (
    id serial NOT NULL,
    firstname character varying NOT NULL,
    surname character varying NOT NULL,
    email character varying NOT NULL,
    username character varying NOT NULL,
    "password" character varying NOT NULL,
    company character varying NOT NULL,
    site_address character varying NOT NULL,
    account_expires timestamp without time zone NOT NULL,
    company_id integer,
    current_plan_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    enabled boolean DEFAULT true NOT NULL
);


--
-- Name: tactile_accounts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('tactile_accounts', 'id'), 8, true);


--
-- Name: tactile_activities; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE tactile_activities (
    id serial NOT NULL,
    name character varying NOT NULL,
    description text,
    type_id integer,
    opportunity_id integer,
    company_id integer,
    person_id integer,
    date date,
    "time" time without time zone,
    later boolean DEFAULT false NOT NULL,
    completed timestamp without time zone,
    assigned_to character varying NOT NULL,
    assigned_by character varying NOT NULL,
    "owner" character varying NOT NULL,
    alteredby character varying NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    usercompanyid bigint NOT NULL
);


--
-- Name: tactile_activities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('tactile_activities', 'id'), 175, true);


--
-- Name: tactile_activities_overview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW tactile_activities_overview AS
    SELECT a.id, a.name, a.description, a.type_id, a.opportunity_id, a.company_id, a.person_id, a.date, a."time", a.later, a.completed, a.assigned_to, a.assigned_by, a."owner", a.alteredby, a.created, a.lastupdated, a.usercompanyid, t.name AS "type", o.name AS opportunity, c.name AS company, (((p.firstname)::text || ' '::text) || (p.surname)::text) AS person, CASE WHEN (a.later = true) THEN false WHEN (a."time" IS NULL) THEN (a.date < (now())::date) ELSE ((a.date + a."time") < timezone((u.timezone)::text, (now())::timestamp without time zone)) END AS overdue, CASE WHEN (a.later = true) THEN 'infinity'::timestamp without time zone WHEN (a."time" IS NULL) THEN (a.date + '23:59:59'::time without time zone) ELSE (a.date + a."time") END AS due FROM (((((tactile_activities a LEFT JOIN activitytype t ON ((t.id = a.type_id))) LEFT JOIN opportunities o ON ((o.id = a.opportunity_id))) LEFT JOIN company c ON ((c.id = a.company_id))) LEFT JOIN person p ON ((p.id = a.person_id))) LEFT JOIN users u ON (((u.username)::text = (a.assigned_to)::text)));


--
-- Name: tactile_magic; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE tactile_magic (
    id serial NOT NULL,
    username character varying NOT NULL,
    "key" character varying NOT NULL,
    value character varying NOT NULL
);


--
-- Name: tactile_magic_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('tactile_magic', 'id'), 58, true);


--
-- Name: tag_map; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE tag_map (
    tag_id integer NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL,
    lastupdated timestamp without time zone DEFAULT now() NOT NULL,
    company_id integer,
    person_id integer,
    opportunity_id integer,
    activity_id integer
);


--
-- Name: tags; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE tags (
    id serial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    created timestamp without time zone DEFAULT now() NOT NULL
);



--
-- Name: notes_overview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW notes_overview AS
    SELECT n.id, n.title, n.note, n.company_id, n.person_id, n.opportunity_id, n.activity_id, n.project_id, n.ticket_id, n."owner", n.alteredby, n.created, n.lastupdated, n.usercompanyid, n.private, n.deleted, c.name AS company, (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, a.name AS activity
   FROM notes n
   LEFT JOIN company c ON c.id = n.company_id
   LEFT JOIN person p ON p.id = n.person_id
   LEFT JOIN opportunities o ON o.id = n.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = n.activity_id;


--
-- Name: tags_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('tags', 'id'), 53, true);



--
-- Name: templates; Type: TABLE; Schema: public; Owner: gj; Tablespace: 
--

CREATE TABLE templates (
    id bigserial NOT NULL,
    name character varying NOT NULL,
    usercompanyid bigint NOT NULL,
    "template" text NOT NULL
);


--
-- Name: templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('templates', 'id'), 1, false);



--
-- Name: user_company_access_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('user_company_access', 'id'), 31, true);


--
-- Name: user_company_accessoverview; Type: VIEW; Schema: public; Owner: te
--

CREATE VIEW user_company_accessoverview AS
    SELECT u.username, u.company_id, u.id, u.enabled, c.name AS company FROM (user_company_access u LEFT JOIN company c ON ((u.company_id = c.id)));


--
-- Name: useroverview; Type: VIEW; Schema: public; Owner: gj
--

CREATE VIEW useroverview AS
    SELECT u.username, u."password", u.enabled, u.lastcompanylogin, u.person_id, uca.company_id AS usercompanyid, (((p.firstname)::text || ' '::text) || (p.surname)::text) AS person FROM ((users u LEFT JOIN user_company_access uca ON (((u.username)::text = (uca.username)::text))) LEFT JOIN person p ON ((u.person_id = p.id)));


--
-- Name: userpreferences; Type: TABLE; Schema: public; Owner: ms; Tablespace: 
--

CREATE TABLE userpreferences (
    id bigserial NOT NULL,
    username character varying NOT NULL,
    module character varying NOT NULL,
    settings character varying
);


--
-- Name: userpreferences_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ms
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('userpreferences', 'id'), 24, true);


--
-- Data for Name: account_plans; Type: TABLE DATA; Schema: public; Owner: gj
--

COPY account_plans (id, name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month) FROM stdin;
2	Micro	20	10000000	2000	10000	6
1	Free	10	10000000	2000	2000	0
\.

--
-- Data for Name: company; Type: TABLE DATA; Schema: public; Owner: pb
--

COPY company (id, name, accountnumber, creditlimit, vatnumber, companynumber, website, employees, usercompanyid, parent_id, "owner", assigned, created, lastupdated, alteredby, description, is_lead, is_account) FROM stdin;
1	Default Company	ABC02	2000	1839949	\N	\N	12	1	\N	greg//tactile	greg//tactile	2007-04-19 14:00:05.15644	2007-10-25 15:31:03.088005	greg//tactile	Default Company specialise in the sourcing and distribution of default values, covering everything from Toasters to Kettles.\n\nThere's information about them on http://www.google.com	f	t
\.


--
-- Data for Name: companyaddress; Type: TABLE DATA; Schema: public; Owner: btf
--

COPY companyaddress (street1, street2, street3, town, county, postcode, countrycode, company_id, name, main, billing, shipping, payment, technical, id) FROM stdin;
45 Acacia Avenue	\N	\N	Bananaville	Bananashire	BA1 3HT	GB	1	Main	t	f	f	f	f	1
\.


--
-- Data for Name: companyroles; Type: TABLE DATA; Schema: public; Owner: ms
--

COPY companyroles (id, companyid, roleid, "read", "write") FROM stdin;
11286	1	2	t	t
\.


--
-- Data for Name: countries; Type: TABLE DATA; Schema: public; Owner: www-data
--

COPY countries (code, name) FROM stdin;
AE	United Arab Emirates
GB	United Kingdom
\.


--
-- Data for Name: hasrole; Type: TABLE DATA; Schema: public; Owner: pb
--

COPY hasrole (roleid, username, id) FROM stdin;
2	greg//tactile	158
1	greg//tactile	159
\.

--
-- Data for Name: lang; Type: TABLE DATA; Schema: public; Owner: www-data
--

COPY lang (code, name) FROM stdin;
EN	English
FR	French
\.

--
-- Data for Name: person; Type: TABLE DATA; Schema: public; Owner: btf
--

COPY person (id, title, firstname, middlename, surname, suffix, department, jobtitle, dob, ni, marital, lang, company_id, "owner", userdetail, reports_to, can_call, can_email, assigned_to, created, lastupdated, alteredby, usercompanyid, crm_source, description) FROM stdin;
1	\N	Greg	\N	Jones	\N	\N	\N	\N	\N	\N	EN	1	greg//tactile	f	\N	f	f	greg//tactile	2007-04-19 14:00:11	2007-04-19 14:00:11	greg//tactile	1	\N	
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: pb
--

COPY roles (id, description, name, usercompanyid) FROM stdin;
1		greg//tactile	1
2		//tactile	1
\.



--
-- Data for Name: system_companies; Type: TABLE DATA; Schema: public; Owner: te
--

COPY system_companies (id, company_id, enabled, theme) FROM stdin;
1	1	t	default
\.


--
-- Data for Name: tactile_accounts; Type: TABLE DATA; Schema: public; Owner: gj
--

COPY tactile_accounts (id, firstname, surname, email, username, "password", company, site_address, account_expires, company_id, current_plan_id, created, lastupdated, enabled) FROM stdin;
1	Greg	Jones	greg.jones@senokian.com	greg	5f4dcc3b5aa765d61d8327deb882cf99	T	tactile	2007-11-30 14:42:47	1	1	2007-10-29 16:35:12.485317	2007-10-29 16:35:12.485317	t
\.

--
-- Data for Name: user_company_access; Type: TABLE DATA; Schema: public; Owner: gj
--

COPY user_company_access (username, company_id, id, enabled) FROM stdin;
greg//tactile	1	2	t
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: pb
--

COPY users (username, "password", lastcompanylogin, person_id, last_login, dropboxkey, is_admin, enabled, timezone, terms_agreed) FROM stdin;
greg//tactile	5f4dcc3b5aa765d61d8327deb882cf99	\N	1	2008-01-03 13:34:40	ubm6x0a8s9wn	t	t	Europe/London	2007-11-26 15:17:45.167886
\.

--
-- Name: account_plans_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY account_plans
    ADD CONSTRAINT account_plans_pkey PRIMARY KEY (id);


--
-- Name: campaign_id_key; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY campaigns
    ADD CONSTRAINT campaign_id_key UNIQUE (id);

--
-- Name: account_statuses_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY account_statuses
    ADD CONSTRAINT account_statuses_name_key UNIQUE (name, usercompanyid);


--
-- Name: account_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY account_statuses
    ADD CONSTRAINT account_statuses_pkey PRIMARY KEY (id);

--
-- Name: company_accountnumber_key; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_accountnumber_key UNIQUE (accountnumber, usercompanyid);


--
-- Name: company_classifications_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_classifications
    ADD CONSTRAINT company_classifications_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_classifications_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_classifications
    ADD CONSTRAINT company_classifications_pkey PRIMARY KEY (id);


--
-- Name: company_contact_methods_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY company_contact_methods
    ADD CONSTRAINT company_contact_methods_pkey PRIMARY KEY (id);


--
-- Name: company_crm_company_id_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_company_id_key UNIQUE (company_id, usercompanyid);


--
-- Name: company_crm_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_pkey PRIMARY KEY (id);


--
-- Name: company_id_key; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_id_key PRIMARY KEY (id);


--
-- Name: company_industries_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_industries
    ADD CONSTRAINT company_industries_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_industries_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_industries
    ADD CONSTRAINT company_industries_pkey PRIMARY KEY (id);


--
-- Name: company_ratings_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_ratings
    ADD CONSTRAINT company_ratings_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_ratings_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_ratings
    ADD CONSTRAINT company_ratings_pkey PRIMARY KEY (id);


--
-- Name: company_sources_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_sources
    ADD CONSTRAINT company_sources_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_sources_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_sources
    ADD CONSTRAINT company_sources_pkey PRIMARY KEY (id);


--
-- Name: company_statuses_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_statuses
    ADD CONSTRAINT company_statuses_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_statuses
    ADD CONSTRAINT company_statuses_pkey PRIMARY KEY (id);


--
-- Name: company_types_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_types
    ADD CONSTRAINT company_types_name_key UNIQUE (name, usercompanyid);


--
-- Name: company_types_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY company_types
    ADD CONSTRAINT company_types_pkey PRIMARY KEY (id);


--
-- Name: companyaddress_pkey; Type: CONSTRAINT; Schema: public; Owner: btf; Tablespace: 
--

ALTER TABLE ONLY companyaddress
    ADD CONSTRAINT companyaddress_pkey PRIMARY KEY (id);

--
-- Name: companypermissions_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY companypermissions
    ADD CONSTRAINT companypermissions_pkey PRIMARY KEY (id);


--
-- Name: companyroles_pkey; Type: CONSTRAINT; Schema: public; Owner: ms; Tablespace: 
--

ALTER TABLE ONLY companyroles
    ADD CONSTRAINT companyroles_pkey PRIMARY KEY (companyid, roleid);


--
-- Name: country_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY countries
    ADD CONSTRAINT country_pkey PRIMARY KEY (code);


--
-- Name: crmactivity_id_key; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT crmactivity_id_key UNIQUE (id);


--
-- Name: crmactivity_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT crmactivity_pkey PRIMARY KEY (name, usercompanyid);


--
-- Name: crmcompanysource_id_key; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY opportunitysource
    ADD CONSTRAINT crmcompanysource_id_key UNIQUE (id);


--
-- Name: crmcompanysource_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY opportunitysource
    ADD CONSTRAINT crmcompanysource_pkey PRIMARY KEY (name, usercompanyid);


--
-- Name: crmopportunity_id_key; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY opportunitystatus
    ADD CONSTRAINT crmopportunity_id_key UNIQUE (id);


--
-- Name: crmopportunity_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY opportunitystatus
    ADD CONSTRAINT crmopportunity_pkey PRIMARY KEY (name, usercompanyid);


--
-- Name: email_preferences_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY email_preferences
    ADD CONSTRAINT email_preferences_pkey PRIMARY KEY (id);


--
-- Name: file_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_pkey PRIMARY KEY (id);

--
-- Name: haspermission_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY haspermission
    ADD CONSTRAINT haspermission_pkey PRIMARY KEY (id);


--
-- Name: hasrole_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY hasrole
    ADD CONSTRAINT hasrole_pkey PRIMARY KEY (id);


--
-- Name: lang_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY lang
    ADD CONSTRAINT lang_pkey PRIMARY KEY (code);

--
-- Name: mail_log_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY mail_log
    ADD CONSTRAINT mail_log_pkey PRIMARY KEY (id);


--
-- Name: notes_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_pkey PRIMARY KEY (id);


--
-- Name: opportunity_pkey; Type: CONSTRAINT; Schema: public; Owner: www-data; Tablespace: 
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT opportunity_pkey PRIMARY KEY (id);


--
-- Name: opportunitytype_pkey; Type: CONSTRAINT; Schema: public; Owner: te; Tablespace: 
--

ALTER TABLE ONLY opportunitytype
    ADD CONSTRAINT opportunitytype_pkey PRIMARY KEY (id);

--
-- Name: payment_records_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY payment_records
    ADD CONSTRAINT payment_records_pkey PRIMARY KEY (id);


--
-- Name: permissions_permission_key; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissions_permission_key UNIQUE (permission);


--
-- Name: permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: person_contact_methods_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY person_contact_methods
    ADD CONSTRAINT person_contact_methods_pkey PRIMARY KEY (id);

--
-- Name: person_pkey; Type: CONSTRAINT; Schema: public; Owner: btf; Tablespace: 
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_pkey PRIMARY KEY (id);

--
-- Name: recently_viewed_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY recently_viewed
    ADD CONSTRAINT recently_viewed_pkey PRIMARY KEY (id);


--
-- Name: remembered_users_pkey; Type: CONSTRAINT; Schema: public; Owner: pmk; Tablespace: 
--

ALTER TABLE ONLY remembered_users
    ADD CONSTRAINT remembered_users_pkey PRIMARY KEY (id);


--
-- Name: roles_name_key; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_name_key UNIQUE (name, usercompanyid);


--
-- Name: roles_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: s3_files_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_pkey PRIMARY KEY (id);

--
-- Name: tactile_accounts_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tactile_accounts
    ADD CONSTRAINT tactile_accounts_pkey PRIMARY KEY (id);


--
-- Name: tactile_accounts_site_address_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tactile_accounts
    ADD CONSTRAINT tactile_accounts_site_address_key UNIQUE (site_address);


--
-- Name: tactile_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_pkey PRIMARY KEY (id);


--
-- Name: tactile_magic_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tactile_magic
    ADD CONSTRAINT tactile_magic_pkey PRIMARY KEY (id);


--
-- Name: tactile_magic_username_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tactile_magic
    ADD CONSTRAINT tactile_magic_username_key UNIQUE (username, "key");


--
-- Name: tags_name_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tags
    ADD CONSTRAINT tags_name_key UNIQUE (name, usercompanyid);


--
-- Name: tags_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: templates_pkey; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY templates
    ADD CONSTRAINT templates_pkey PRIMARY KEY (id);


--
-- Name: user_company_access_username_key; Type: CONSTRAINT; Schema: public; Owner: gj; Tablespace: 
--

ALTER TABLE ONLY user_company_access
    ADD CONSTRAINT user_company_access_username_key UNIQUE (username, company_id);


--
-- Name: userpreferences_pkey; Type: CONSTRAINT; Schema: public; Owner: ms; Tablespace: 
--

ALTER TABLE ONLY userpreferences
    ADD CONSTRAINT userpreferences_pkey PRIMARY KEY (username, module);


--
-- Name: users_person_id_key; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_person_id_key UNIQUE (person_id);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: pb; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (username);

--
-- Name: ccm_company_id; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX ccm_company_id ON company_contact_methods USING btree (company_id);


--
-- Name: company_client_view; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX company_client_view ON company USING btree (usercompanyid, is_lead);


--
-- Name: company_contact_methods_company_id_main_type; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX company_contact_methods_company_id_main_type ON company_contact_methods USING btree (company_id, main, "type");


--
-- Name: company_overview; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX company_overview ON company USING btree (usercompanyid, "owner", is_lead) WHERE (is_lead = false);


--
-- Name: company_tree; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX company_tree ON company USING btree (usercompanyid, parent_id);


--
-- Name: companyaddress_company_id; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX companyaddress_company_id ON companyaddress USING btree (company_id);


--
-- Name: companyaddress_company_id_main; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX companyaddress_company_id_main ON companyaddress USING btree (company_id, main);


--
-- Name: companyaddress_main; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX companyaddress_main ON companyaddress USING btree (main);


--
-- Name: companycontactmethod_main_type; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX companycontactmethod_main_type ON company_contact_methods USING btree (main, "type");

--
-- Name: companyroles_companyid_read; Type: INDEX; Schema: public; Owner: ms; Tablespace: 
--

CREATE INDEX companyroles_companyid_read ON companyroles USING btree (companyid, "read") WHERE "read";


--
-- Name: companyroles_roleid; Type: INDEX; Schema: public; Owner: ms; Tablespace: 
--

CREATE INDEX companyroles_roleid ON companyroles USING btree (roleid);

--
-- Name: crmactivity_companyid; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX crmactivity_companyid ON activitytype USING btree (usercompanyid);


--
-- Name: crmcompanysource_name; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX crmcompanysource_name ON opportunitysource USING btree (name);


--
-- Name: crmopportunity_companyid; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX crmopportunity_companyid ON opportunitystatus USING btree (usercompanyid);



--
-- Name: haspermission_permissionsid_index; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX haspermission_permissionsid_index ON haspermission USING btree (permissionsid);


--
-- Name: haspermission_roleid_index; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX haspermission_roleid_index ON haspermission USING btree (roleid);


--
-- Name: haspermission_roleid_permissionsid_index; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX haspermission_roleid_permissionsid_index ON haspermission USING btree (roleid, permissionsid);


--
-- Name: hasrole_roleid_username; Type: INDEX; Schema: public; Owner: pb; Tablespace: 
--

CREATE INDEX hasrole_roleid_username ON hasrole USING btree (roleid, username);


--
-- Name: notes_company; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX notes_company ON notes USING btree (company_id, usercompanyid);


--
-- Name: opportunities_all_index; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX opportunities_all_index ON opportunities USING btree (usercompanyid);


--
-- Name: opportunities_full_join_index; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX opportunities_full_join_index ON opportunities USING btree (status_id, type_id, source_id, campaign_id, person_id, company_id, usercompanyid);


--
-- Name: opportunities_join_index; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX opportunities_join_index ON opportunities USING btree (usercompanyid, company_id, person_id);


--
-- Name: opportunity_name; Type: INDEX; Schema: public; Owner: www-data; Tablespace: 
--

CREATE INDEX opportunity_name ON opportunities USING btree (name);


--
-- Name: person_company_id; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX person_company_id ON person USING btree (company_id);


--
-- Name: person_contact_methods_person_id_index; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX person_contact_methods_person_id_index ON person_contact_methods USING btree (person_id);


--
-- Name: person_contact_methods_person_id_with_main_index; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX person_contact_methods_person_id_with_main_index ON person_contact_methods USING btree (main, "type", person_id);


--
-- Name: person_contact_methods_type_main; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX person_contact_methods_type_main ON person_contact_methods USING btree ("type", main);


--
-- Name: person_owner; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX person_owner ON person USING btree ("owner");


--
-- Name: person_surname; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX person_surname ON person USING btree (surname);


--
-- Name: person_usercompanyid; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX person_usercompanyid ON person USING btree (usercompanyid);


--
-- Name: personaddress_for_overview; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX personaddress_for_overview ON personaddress USING btree (person_id, main);


--
-- Name: personaddress_person_id; Type: INDEX; Schema: public; Owner: btf; Tablespace: 
--

CREATE INDEX personaddress_person_id ON personaddress USING btree (person_id);


--
-- Name: tag_map_company; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX tag_map_company ON tag_map USING btree (tag_id, company_id);


--
-- Name: tag_map_person; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX tag_map_person ON tag_map USING btree (tag_id, person_id);


--
-- Name: tag_map_tag_id_company_id; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE UNIQUE INDEX tag_map_tag_id_company_id ON tag_map USING btree (tag_id, company_id) WHERE (company_id IS NOT NULL);


--
-- Name: tags_name; Type: INDEX; Schema: public; Owner: gj; Tablespace: 
--

CREATE INDEX tags_name ON tags USING btree (name);


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY users
    ADD CONSTRAINT "$1" FOREIGN KEY (lastcompanylogin) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunitystatus
    ADD CONSTRAINT "$1" FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunitysource
    ADD CONSTRAINT "$1" FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$1" FOREIGN KEY (status_id) REFERENCES opportunitystatus(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY activitytype
    ADD CONSTRAINT "$1" FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY company_contact_methods
    ADD CONSTRAINT "$1" FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$2" FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON UPDATE CASCADE ON DELETE SET NULL;

--
-- Name: $3; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$3" FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE SET NULL;



--
-- Name: $4; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$4" FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: $5; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$5" FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $6; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$6" FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;



--
-- Name: $7; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$7" FOREIGN KEY (source_id) REFERENCES opportunitysource(id) ON UPDATE CASCADE ON DELETE SET NULL;

--
-- Name: $8; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$8" FOREIGN KEY (assigned) REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: $9; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT "$9" FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: account_statuses_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY account_statuses
    ADD CONSTRAINT account_statuses_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;




--
-- Name: assigned_fk; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY company
    ADD CONSTRAINT assigned_fk FOREIGN KEY (assigned) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cc_fk; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY companyaddress
    ADD CONSTRAINT cc_fk FOREIGN KEY (countrycode) REFERENCES countries(code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: cc_fk; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY personaddress
    ADD CONSTRAINT cc_fk FOREIGN KEY (countrycode) REFERENCES countries(code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_classifications_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_classifications
    ADD CONSTRAINT company_classifications_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_crm_account_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_account_status_id_fkey FOREIGN KEY (account_status_id) REFERENCES account_statuses(id) ON UPDATE CASCADE;


--
-- Name: company_crm_classification_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_classification_id_fkey FOREIGN KEY (classification_id) REFERENCES company_classifications(id) ON UPDATE CASCADE;


--
-- Name: company_crm_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_crm_industry_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_industry_id_fkey FOREIGN KEY (industry_id) REFERENCES company_industries(id) ON UPDATE CASCADE;


--
-- Name: company_crm_rating_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_rating_id_fkey FOREIGN KEY (rating_id) REFERENCES company_ratings(id) ON UPDATE CASCADE;


--
-- Name: company_crm_source_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_source_id_fkey FOREIGN KEY (source_id) REFERENCES company_sources(id) ON UPDATE CASCADE;


--
-- Name: company_crm_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_status_id_fkey FOREIGN KEY (status_id) REFERENCES company_statuses(id) ON UPDATE CASCADE;


--
-- Name: company_crm_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_type_id_fkey FOREIGN KEY (type_id) REFERENCES company_types(id) ON UPDATE CASCADE;


--
-- Name: company_crm_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_crm
    ADD CONSTRAINT company_crm_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_fk; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY companyaddress
    ADD CONSTRAINT company_fk FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_industries_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_industries
    ADD CONSTRAINT company_industries_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;



--
-- Name: company_parent_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_parent_id_fkey FOREIGN KEY (parent_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_ratings_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_ratings
    ADD CONSTRAINT company_ratings_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;



--
-- Name: company_sources_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_sources
    ADD CONSTRAINT company_sources_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_statuses_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_statuses
    ADD CONSTRAINT company_statuses_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_types_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY company_types
    ADD CONSTRAINT company_types_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: company_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY company
    ADD CONSTRAINT company_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: companypermissions_permissionid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY companypermissions
    ADD CONSTRAINT companypermissions_permissionid_fkey FOREIGN KEY (permissionid) REFERENCES permissions(id);


--
-- Name: companypermissions_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY companypermissions
    ADD CONSTRAINT companypermissions_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: companyroles_companyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ms
--

ALTER TABLE ONLY companyroles
    ADD CONSTRAINT companyroles_companyid_fkey FOREIGN KEY (companyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: companyroles_roleid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ms
--

ALTER TABLE ONLY companyroles
    ADD CONSTRAINT companyroles_roleid_fkey FOREIGN KEY (roleid) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: email_preferences_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY email_preferences
    ADD CONSTRAINT email_preferences_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;



--
-- Name: file_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id);


--
-- Name: fk; Type: FK CONSTRAINT; Schema: public; Owner: www-data
--

ALTER TABLE ONLY opportunities
    ADD CONSTRAINT fk FOREIGN KEY (type_id) REFERENCES opportunitytype(id) ON UPDATE CASCADE ON DELETE CASCADE;



--
-- Name: haspermission_permissionsid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY haspermission
    ADD CONSTRAINT haspermission_permissionsid_fkey FOREIGN KEY (permissionsid) REFERENCES permissions(id);


--
-- Name: haspermission_roleid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY haspermission
    ADD CONSTRAINT haspermission_roleid_fkey FOREIGN KEY (roleid) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hasrole_roleid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY hasrole
    ADD CONSTRAINT hasrole_roleid_fkey FOREIGN KEY (roleid) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: hasrole_username_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY hasrole
    ADD CONSTRAINT hasrole_username_fkey FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notes_alteredby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_alteredby_fkey FOREIGN KEY (alteredby) REFERENCES users(username);


--
-- Name: notes_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notes_opportunity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_opportunity_id_fkey FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notes_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username);


--
-- Name: notes_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: notes_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY notes
    ADD CONSTRAINT notes_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: owner_foreign_key; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY company
    ADD CONSTRAINT owner_foreign_key FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: payment_records_account_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY payment_records
    ADD CONSTRAINT payment_records_account_id_fkey FOREIGN KEY (account_id) REFERENCES tactile_accounts(id) ON UPDATE CASCADE;


--
-- Name: payment_records_payment_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY payment_records
    ADD CONSTRAINT payment_records_payment_id_fkey FOREIGN KEY (payment_id) REFERENCES payment_records(id) ON UPDATE CASCADE;


--
-- Name: person_alteredby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_alteredby_fkey FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE;


--
-- Name: person_assigned_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_assigned_fkey FOREIGN KEY (assigned_to) REFERENCES users(username) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: person_companyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_companyid_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: person_contact_methods_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY person_contact_methods
    ADD CONSTRAINT person_contact_methods_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: person_crm_source_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_crm_source_fkey FOREIGN KEY (crm_source) REFERENCES company_sources(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: person_fk; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY personaddress
    ADD CONSTRAINT person_fk FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: person_lang_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_lang_fkey FOREIGN KEY (lang) REFERENCES lang(code) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: person_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE;


--
-- Name: person_reportsto_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_reportsto_fkey FOREIGN KEY (reports_to) REFERENCES person(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: person_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: btf
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: recently_viewed_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY recently_viewed
    ADD CONSTRAINT recently_viewed_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: remembered_users_username_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pmk
--

ALTER TABLE ONLY remembered_users
    ADD CONSTRAINT remembered_users_username_fkey FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: roles_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id);


--
-- Name: s3_files_activity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_activity_id_fkey FOREIGN KEY (activity_id) REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: s3_files_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: s3_files_opportunity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_opportunity_id_fkey FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: s3_files_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username);


--
-- Name: s3_files_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: s3_files_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY s3_files
    ADD CONSTRAINT s3_files_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id);


--
-- Name: system_companies_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: te
--

ALTER TABLE ONLY system_companies
    ADD CONSTRAINT system_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_accounts_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_accounts
    ADD CONSTRAINT tactile_accounts_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE;


--
-- Name: tactile_accounts_current_plan_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_accounts
    ADD CONSTRAINT tactile_accounts_current_plan_id_fkey FOREIGN KEY (current_plan_id) REFERENCES account_plans(id) ON UPDATE CASCADE;


--
-- Name: tactile_activities_alteredby_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_alteredby_fkey FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_assigned_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_assigned_by_fkey FOREIGN KEY (assigned_by) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_opportunity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_opportunity_id_fkey FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_owner_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_owner_fkey FOREIGN KEY ("owner") REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_activities_type_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_type_id_fkey FOREIGN KEY (type_id) REFERENCES activitytype(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: tactile_activities_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_activities
    ADD CONSTRAINT tactile_activities_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tactile_magic_username_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tactile_magic
    ADD CONSTRAINT tactile_magic_username_fkey FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tag_map_activity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tag_map
    ADD CONSTRAINT tag_map_activity_id_fkey FOREIGN KEY (activity_id) REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tag_map_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tag_map
    ADD CONSTRAINT tag_map_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tag_map_opportunity_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tag_map
    ADD CONSTRAINT tag_map_opportunity_id_fkey FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tag_map_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tag_map
    ADD CONSTRAINT tag_map_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tag_map_tag_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tag_map
    ADD CONSTRAINT tag_map_tag_id_fkey FOREIGN KEY (tag_id) REFERENCES tags(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: tags_usercompanyid_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY tags
    ADD CONSTRAINT tags_usercompanyid_fkey FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;

--
-- Name: user_company_access_company_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY user_company_access
    ADD CONSTRAINT user_company_access_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_company_access_username_fkey; Type: FK CONSTRAINT; Schema: public; Owner: gj
--

ALTER TABLE ONLY user_company_access
    ADD CONSTRAINT user_company_access_username_fkey FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE;


--
-- Name: userpreferences_username_fkey; Type: FK CONSTRAINT; Schema: public; Owner: ms
--

ALTER TABLE ONLY userpreferences
    ADD CONSTRAINT userpreferences_username_fkey FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: users_person_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_person_id_fkey FOREIGN KEY (person_id) REFERENCES person(id);


--
-- Name: alteredby_fk; Type: FK CONSTRAINT; Schema: public; Owner: pb
--

ALTER TABLE ONLY company
    ADD CONSTRAINT "alteredby_fk" FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;



CREATE TABLE emails(
id serial not null primary key,
person_id int references person(id) on update cascade on delete set null,
company_id int references company(id) on update cascade on delete cascade,
email_from varchar not null,
email_to varchar not null,
subject varchar,
body text,
received timestamp not null default now(),
created timestamp not null default now(),
owner varchar not null references users(username) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
opportunity_id integer references opportunities(id) on update cascade on delete set null
);

ALTER TABLE s3_files ADD COLUMN email_id integer;

CREATE VIEW usersoverview AS SELECT u.username, u."password", u.enabled, u.lastcompanylogin, u.person_id, uca.company_id AS usercompanyid, (p.firstname::text || ' '::text) || p.surname::text AS person, u.dropboxkey
   FROM users u
   LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
   LEFT JOIN person p ON u.person_id = p.id;
CREATE VIEW email_overview AS
    SELECT e.id, e.person_id, e.company_id, e.opportunity_id, e.email_from, e.email_to, 
           e.subject, e.body, e.received, e.created, e."owner", e.usercompanyid, c.name AS company, 
           (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, 
            CASE
                WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::text
                WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::text
                ELSE ''::text
            END AS direction
    FROM emails e
	LEFT JOIN users u ON (u.username = e.owner)
    LEFT JOIN person_contact_methods pcm ON (pcm.person_id = u.person_id) AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
    LEFT JOIN person p ON e.person_id = p.id
    LEFT JOIN company c ON e.company_id = c.id
    LEFT JOIN opportunities o ON e.opportunity_id = o.id;

ALTER TABLE users ADD date_format VARCHAR NOT NULL default 'd/m/Y';

ALTER TABLE person ADD private BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE users ADD webkey VARCHAR(32);

ALTER TABLE tactile_activities ADD class VARCHAR NOT NULL DEFAULT 'todo';
ALTER TABLE tactile_activities ADD location VARCHAR;
ALTER TABLE tactile_activities ADD end_date DATE;
ALTER TABLE tactile_activities ADD end_time TIME;
DROP VIEW tactile_activities_overview;
CREATE VIEW tactile_activities_overview AS
	SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id, a.company_id, a.person_id,
		a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to, a.assigned_by,
		a."owner", a.alteredby, a.created, a.lastupdated, a.usercompanyid,
		t.name AS "type", o.name AS opportunity, c.name AS company,
		(p.firstname::text || ' '::text) || p.surname::text AS person,
	CASE
		WHEN a.later = true THEN false
		WHEN a."time" IS NULL THEN a.date < now()::date
		ELSE (a.date + a."time") < timezone(u.timezone::text, now()::timestamp without time zone)
		END AS overdue,
	CASE
		WHEN a.later = true THEN 'infinity'::timestamp without time zone
		WHEN a."time" IS NULL THEN a.date + '23:59:59'::time without time zone
		ELSE a.date + a."time"
		END AS due
	FROM tactile_activities a
	LEFT JOIN activitytype t ON t.id = a.type_id
	LEFT JOIN opportunities o ON o.id = a.opportunity_id
	LEFT JOIN company c ON c.id = a.company_id
	LEFT JOIN person p ON p.id = a.person_id
	LEFT JOIN users u ON u.username::text = a.assigned_to::text;


create index company_created on company(created);


DROP VIEW companyoverview;
DROP VIEW companyoverview2;

alter table company drop is_lead;

CREATE VIEW companyoverview AS
SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, 
        CASE
            WHEN hr.username IS NULL THEN c."owner"
            ELSE hr.username
        END AS usernameaccess
   FROM company c
   LEFT JOIN companyaddress ca ON c.id = ca.company_id AND ca.main
   LEFT JOIN company_contact_methods p ON c.id = p.company_id AND p.main AND p."type" = 'T'::bpchar
   LEFT JOIN company_contact_methods f ON c.id = f.company_id AND f.main AND f."type" = 'F'::bpchar
   LEFT JOIN company_contact_methods e ON c.id = e.company_id AND e.main AND e."type" = 'E'::bpchar
   LEFT JOIN companyroles cr ON c.id = cr.companyid AND cr."read"
   LEFT JOIN hasrole hr ON cr.roleid = hr.roleid
UNION 
 SELECT c.id, c.name, c.accountnumber, c.creditlimit, c.vatnumber, c.companynumber, c.website, c.employees, c.usercompanyid, c.parent_id, c."owner", c.assigned, c.created, c.lastupdated, c.alteredby, c.description, ca.street1, ca.street2, ca.street3, ca.town, ca.county, ca.postcode, ca.countrycode, p.contact AS phone, e.contact AS email, f.contact AS fax, c."owner" AS usernameaccess
   FROM company c
   LEFT JOIN companyaddress ca ON c.id = ca.company_id AND ca.main
   LEFT JOIN company_contact_methods p ON c.id = p.company_id AND p.main AND p."type" = 'T'::bpchar
   LEFT JOIN company_contact_methods f ON c.id = f.company_id AND f.main AND f."type" = 'F'::bpchar
   LEFT JOIN company_contact_methods e ON c.id = e.company_id AND e.main AND e."type" = 'E'::bpchar;

alter table tag_map add id serial not null primary key; 
alter table tag_map add column hash varchar;

ALTER TABLE tactile_accounts ADD freshbooks_account varchar;
ALTER TABLE tactile_accounts ADD freshbooks_token varchar;

ALTER TABLE company ADD freshbooks_id int;

ALTER TABLE opportunities ADD archived BOOLEAN NOT NULL DEFAULT FALSE;

drop view opportunitiesoverview;
CREATE VIEW opportunitiesoverview AS
SELECT o.id, o.status_id, o.campaign_id, o.company_id, o.person_id, 
o."owner", o.name, o.description, o.cost, o.probability, o.enddate, 
o.usercompanyid, o.type_id, o.source_id, o.nextstep, o.assigned, 
o.created, o.lastupdated, o.alteredby, o.archived,
c.name AS company, 
(p.firstname::text || ' '::text) || p.surname::text AS person, 
os.name AS source, ot.name AS "type", 
opportunitystatus.name AS status, 
COALESCE (opportunitystatus.open, false) AS open, 
COALESCE(opportunitystatus.won, false) AS won
   FROM opportunities o
   LEFT JOIN company c ON o.company_id = c.id
   LEFT JOIN person p ON o.person_id = p.id
   LEFT JOIN opportunitysource os ON o.source_id = os.id
   LEFT JOIN opportunitytype ot ON o.type_id = ot.id
   LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id;
ALTER TABLE tactile_accounts ADD signup_code varchar;

--
-- omelette/sql/s3_files_overview_241.sql
--

CREATE VIEW s3_files_overview AS
SELECT f.id, f.bucket, f.object, f.filename, f.content_type, f."size", f.extension, f.comment, f."owner", f.created, f.usercompanyid, f.company_id, c.name AS company, f.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person, f.opportunity_id, o.name AS opportunity, f.activity_id, a.name AS activity, f.email_id, e.subject as email
   FROM s3_files f
   LEFT JOIN company c ON c.id = f.company_id
   LEFT JOIN person p ON p.id = f.person_id
   LEFT JOIN opportunities o ON o.id = f.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = f.activity_id
   LEFT JOIN emails e ON e.id = f.email_id;

--
-- emailopps.sql
--

DROP VIEW email_overview;
CREATE VIEW email_overview AS
    SELECT e.id, e.person_id, e.company_id, e.opportunity_id, e.email_from, e.email_to, 
           e.subject, e.body, e.received, e.created, e."owner", e.usercompanyid, c.name AS company, 
           (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, 
            CASE
                WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::text
                WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::text
                ELSE ''::text
            END AS direction
    FROM emails e
	LEFT JOIN users u ON (u.username = e.owner)
    LEFT JOIN person_contact_methods pcm ON (pcm.person_id = u.person_id) AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
    LEFT JOIN person p ON e.person_id = p.id
    LEFT JOIN company c ON e.company_id = c.id
    LEFT JOIN opportunities o ON e.opportunity_id = o.id;

ALTER TABLE mail_log ADD COLUMN html boolean DEFAULT false;

--
-- resolve tables
--

CREATE TABLE queues (
id serial not null primary key,
name varchar not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
UNIQUE (name, usercompanyid)
);

ALTER TABLE queues ADD description text;
ALTER TABLE queues ADD email varchar;


CREATE TABLE queue_users (
queue_id int not null references queues(id) on update cascade on delete cascade,
username varchar not null references users(username) on update cascade on delete cascade
);

CREATE TABLE ticket_statuses(
id serial not null primary key,
name varchar not null,
position int not null default 1,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE tickets (
id serial not null primary key,
summary varchar not null,
status_id int not null references ticket_statuses(id) on update cascade on delete cascade,
created timestamp not null default now(),
lastupdated timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

ALTER TABLE tickets ADD assigned_to varchar references users(username) on update cascade on delete cascade;

alter table tickets add alteredby varchar not null references users(username) on update cascade on delete cascade;

ALTER TABLE ticket_statuses ADD is_new boolean not null default false;
ALTER TABLE ticket_statuses ADD open boolean not null default false;
ALTER TABLE ticket_statuses ADD waiting boolean not null default false;
ALTER TABLE ticket_statuses ADD solved boolean not null default false;
ALTER TABLE ticket_statuses ADD closed boolean not null default false;

ALTER TABLE tickets ADD company_id int references company(id);
ALTER TABLE tickets ADD person_id int references person(id);

ALTER TABLE tickets ADD queue_id int references queues(id);

CREATE TABLE ticket_changesets (
id serial not null primary key,
ticket_id int not null references tickets(id) on update cascade on delete cascade,
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
source varchar not null,
owner varchar not null references users(username) on update cascade on delete cascade
);

ALTER TABLE ticket_changesets ALTER owner DROP NOT NULL;
ALTER TABLE ticket_changesets ADD person_id int references person(id);

CREATE TABLE ticket_comments (
id serial not null primary key,
ticket_id int not null references tickets(id) on update cascade on delete cascade,
changeset_id int not null references ticket_changesets(id) on update cascade on delete cascade,
body text,
created timestamp not null default now()
);

ALTER TABLE ticket_comments ADD email_from varchar;
ALTER TABLE ticket_comments ADD email_to varchar;

CREATE TABLE ticket_changes (
id serial not null,
changeset_id int not null references ticket_changesets(id) on update cascade on delete cascade,
property varchar not null,
before_value varchar not null default '-',
after_value varchar not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE ticket_triggers (
id serial not null primary key,
name varchar not null,
active boolean not null default true,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE trigger_conditions (
id serial not null primary key,
trigger_id int not null references ticket_triggers(id) on update cascade on delete cascade,
type varchar not null,
operator varchar not null default '=',
value varchar,
status_id int references ticket_statuses(id) on update cascade on delete cascade,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE trigger_actions (
id serial not null primary key,
trigger_id int not null references ticket_triggers(id) on update cascade on delete cascade,
type varchar not null,
value varchar,
to_notify varchar,
notify_user varchar references users(username) on update cascade on delete cascade,
email_subject varchar,
email_body text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
ALTER TABLE ticket_changes ADD ticket_id int NOT NULL references tickets(id) on update cascade on delete cascade;
alter table tickets drop alteredby;
alter table tickets add sender_email varchar;
alter table ticket_comments add private boolean not null default false;

alter table s3_files add ticket_id int references tickets(id) on update cascade on delete cascade;

alter table s3_files alter owner drop not null;

CREATE TABLE notification_recipients (
id serial not null primary key,
action_id int not null references trigger_actions(id) on update cascade on delete cascade,
recipient varchar,
recipient_type varchar,
auth varchar
);

ALTER TABLE trigger_actions drop to_notify, drop notify_user;

DROP VIEW email_overview;

CREATE VIEW email_overview AS
SELECT e.id, e.person_id, e.company_id, e.email_from, e.email_to, e.subject, e.body, e.received, e.created,
	e."owner", e.usercompanyid, c.name AS company, 
	(p.firstname::text || ' '::text) || p.surname::text AS person,
	CASE WHEN pcm.contact=e.email_from THEN 'incoming' ELSE 'outgoing' END AS direction
   FROM emails e
	JOIN person_contact_methods pcm ON (e.person_id=pcm.person_id AND (e.email_from=pcm.contact OR e.email_to=pcm.contact))
   LEFT JOIN person p ON e.person_id = p.id
   LEFT JOIN company c ON e.company_id = c.id;

create index emails_usercompanyid on emails(usercompanyid);
create index notes_opportunity_id on notes(opportunity_id);
create index notes_recent on notes (usercompanyid, lastupdated);
create index emails_recent on emails(received);
create index recently_viewed_created on recently_viewed (created);
analyze emails;
analyze notes;
analyze recently_viewed;

CREATE TABLE notification_records (
id serial not null primary key,
changeset_id int not null references ticket_changesets(id) on update cascade on delete cascade,
ticket_id int not null references tickets(id) on update cascade on delete cascade,
trigger_name varchar not null,
recipient varchar not null,
method varchar not null,
created timestamp not null default now()
);

ALTER TABLE users ADD resolve_enabled boolean not null default false;
UPDATE users SET resolve_enabled = 'true';

ALTER TABLE trigger_conditions DROP status_id;
ALTER TABLE ticket_triggers ADD position int;

update trigger_actions set type='EmailNotify' where type='email_notify';
update trigger_actions set type='TwitterNotify' where type='twitter_notify';
update trigger_actions set type='StatusChange' where type='status_change';

CREATE VIEW comment_overview AS
SELECT c.*, t.summary AS ticket, t.usercompanyid FROM ticket_comments c JOIN tickets t ON (c.ticket_id=t.id);

CREATE TABLE ticket_views (
id serial not null primary key,
ticket_id int not null references tickets(id) ON UPDATE CASCADE ON DELETE CASCADE,
username varchar not null references users(username) ON UPDATE CASCADE ON DELETE CASCADE,
created timestamp not null default now()
);
ALTER TABLE tactile_accounts ADD resolve_enabled boolean not null default false;
UPDATE tactile_accounts SET resolve_enabled=true where site_address in ('tactile');

ALTER TABLE s3_files ADD changeset_id int references ticket_changesets on update cascade on delete set null;

--
-- api.sql
--

ALTER TABLE users ADD COLUMN api_token VARCHAR UNIQUE;

ALTER TABLE tactile_accounts ADD COLUMN tactile_api_enabled BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE company ADD COLUMN status_id INT REFERENCES company_statuses(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN source_id INT REFERENCES company_sources(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN classification_id INT REFERENCES company_classifications(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN rating_id INT REFERENCES company_ratings(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN industry_id INT REFERENCES company_industries(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN type_id INT REFERENCES company_types(id) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE company SET
status_id = company_crm.status_id,
source_id = company_crm.source_id,
classification_id = company_crm.classification_id,
rating_id = company_crm.rating_id,
industry_id = company_crm.industry_id,
type_id = company_crm.type_id
FROM company_crm WHERE company_crm.company_id = company.id;
DROP TABLE company_crm;

ALTER TABLE company ADD COLUMN street1 VARCHAR;
ALTER TABLE company ADD COLUMN street2 VARCHAR;
ALTER TABLE company ADD COLUMN street3 VARCHAR;
ALTER TABLE company ADD COLUMN town VARCHAR;
ALTER TABLE company ADD COLUMN county VARCHAR;
ALTER TABLE company ADD COLUMN postcode VARCHAR;
ALTER TABLE company ADD COLUMN country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE company SET
street1 = ca.street1,
street2 = ca.street2,
street3 = ca.street3,
town = ca.town,
county = ca.county,
postcode = ca.postcode,
country_code = ca.countrycode
FROM companyaddress ca WHERE main AND company.id = ca.company_id;
DROP TABLE companyaddress CASCADE;

ALTER TABLE person ADD COLUMN street1 VARCHAR;
ALTER TABLE person ADD COLUMN street2 VARCHAR;
ALTER TABLE person ADD COLUMN street3 VARCHAR;
ALTER TABLE person ADD COLUMN town VARCHAR;
ALTER TABLE person ADD COLUMN county VARCHAR;
ALTER TABLE person ADD COLUMN postcode VARCHAR;
ALTER TABLE person ADD COLUMN country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE person SET
street1 = ca.street1,
street2 = ca.street2,
street3 = ca.street3,
town = ca.town,
county = ca.county,
postcode = ca.postcode,
country_code = ca.countrycode
FROM personaddress ca WHERE main AND person.id = ca.person_id;
DROP TABLE personaddress CASCADE;


--
-- fixing things.sql
--

ALTER TABLE company RENAME COLUMN assigned TO assigned_to;
ALTER TABLE opportunities RENAME COLUMN assigned TO assigned_to;

ALTER TABLE company RENAME TO organisations;
ALTER TABLE company_contact_methods RENAME TO organisation_contact_methods;
ALTER TABLE companyroles RENAME TO organisation_roles;

ALTER TABLE organisations ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE organisations ALTER COLUMN lastupdated TYPE timestamp(0);

-- foobaroverview to foobar_overview?
ALTER VIEW companyrolesoverview RENAME TO organisation_roles_overview;

ALTER TABLE tactile_activities RENAME COLUMN company_id TO organisation_id;
DROP VIEW tactile_activities_overview;
ALTER TABLE tactile_activities ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE tactile_activities ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW tactile_activities_overview AS
SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id,
 a.organisation_id, a.person_id, a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to,
  a.assigned_by, a.owner, a.alteredby, a.created, a.lastupdated, a.usercompanyid, t.name AS type,
   o.name AS opportunity, org.name AS organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, 
        CASE
            WHEN a.later = true THEN false
            WHEN a."time" IS NULL THEN a.date < now()::date
            ELSE (a.date + a."time") < timezone(u.timezone::text, now()::timestamp without time zone)
        END AS overdue, 
        CASE
            WHEN a.later = true THEN 'infinity'::timestamp without time zone
            WHEN a."time" IS NULL THEN a.date + '23:59:59'::time without time zone
            ELSE a.date + a."time"
        END AS due
   FROM tactile_activities a
   LEFT JOIN activitytype t ON t.id = a.type_id
   LEFT JOIN opportunities o ON o.id = a.opportunity_id
   LEFT JOIN organisations org ON org.id = a.organisation_id
   LEFT JOIN person p ON p.id = a.person_id
   LEFT JOIN users u ON u.username::text = a.assigned_to::text;

ALTER TABLE opportunities RENAME COLUMN company_id TO organisation_id;
DROP VIEW opportunitiesoverview;
ALTER TABLE opportunities ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE opportunities ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW opportunities_overview AS
SELECT o.id, o.status_id, o.campaign_id, o.organisation_id, o.person_id, o.owner, o.name,
 o.description, o.cost, o.probability, o.enddate, o.usercompanyid, o.type_id, o.source_id,
  o.nextstep, o.assigned_to, o.created, o.lastupdated, o.alteredby, o.archived, org.name AS organisation,
   (p.firstname::text || ' '::text) || p.surname::text AS person, os.name AS source, ot.name AS type,
    opportunitystatus.name AS status, COALESCE(opportunitystatus.open, false) AS open, 
    COALESCE(opportunitystatus.won, false) AS won
   FROM opportunities o
   LEFT JOIN organisations org ON o.organisation_id = org.id
   LEFT JOIN person p ON o.person_id = p.id
   LEFT JOIN opportunitysource os ON o.source_id = os.id
   LEFT JOIN opportunitytype ot ON o.type_id = ot.id
   LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id;


ALTER TABLE person RENAME COLUMN company_id TO organisation_id;
ALTER TABLE person RENAME COLUMN lang TO language_code;
ALTER TABLE person RENAME TO people;
DROP VIEW personoverview;
DROP VIEW personoverview2;
ALTER TABLE people ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE people ALTER COLUMN lastupdated TYPE timestamp(0);

ALTER TABLE organisation_contact_methods RENAME COLUMN company_id TO organisation_id;
ALTER TABLE organisation_roles RENAME COLUMN companyid TO organisation_id;

-- email_overview to emails_overview?
ALTER TABLE emails RENAME COLUMN company_id TO organisation_id;
DROP VIEW email_overview;
ALTER TABLE emails ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE emails ALTER COLUMN received TYPE timestamp(0);
CREATE VIEW emails_overview AS 
SELECT e.id, e.person_id, e.organisation_id, e.opportunity_id, e.email_from, e.email_to, e.subject,
 e.body, e.received, e.created, e.owner, e.usercompanyid, org.name AS organisation,
  (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::text
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::text
            ELSE ''::text
        END AS direction
   FROM emails e
   LEFT JOIN users u ON u.username::text = e.owner::text
   LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
   LEFT JOIN people p ON e.person_id = p.id
   LEFT JOIN organisations org ON e.organisation_id = org.id
   LEFT JOIN opportunities o ON e.opportunity_id = o.id;

ALTER TABLE notes RENAME COLUMN company_id TO organisation_id;
DROP VIEW notes_overview;
ALTER TABLE notes ALTER COLUMN created TYPE timestamp(0);
ALTER TABLE notes ALTER COLUMN lastupdated TYPE timestamp(0);
CREATE VIEW notes_overview AS
SELECT n.id, n.title, n.note, n.organisation_id, n.person_id, n.opportunity_id, 
n.activity_id, n.project_id, n.ticket_id, n.owner, n.alteredby, n.created,
n.lastupdated, n.usercompanyid, n.private, n.deleted, org.name AS organisation,
(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS opportunity, a.name AS activity
FROM notes n
LEFT JOIN organisations org ON org.id = n.organisation_id
LEFT JOIN people p ON p.id = n.person_id
LEFT JOIN opportunities o ON o.id = n.opportunity_id
LEFT JOIN tactile_activities a ON a.id = n.activity_id;

ALTER TABLE s3_files RENAME COLUMN company_id TO organisation_id;
DROP VIEW s3_files_overview;
CREATE VIEW s3_files_overview AS
SELECT f.id, f.bucket, f.object, f.filename, f.content_type, f.size,
 f.extension, f.comment, f.owner, f.created, f.usercompanyid, f.organisation_id,
  org.name AS organisation, f.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person,
   f.opportunity_id, o.name AS opportunity, f.activity_id, a.name AS activity, f.email_id, 
   e.subject AS email
   FROM s3_files f
   LEFT JOIN organisations org ON org.id = f.organisation_id
   LEFT JOIN people p ON p.id = f.person_id
   LEFT JOIN opportunities o ON o.id = f.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = f.activity_id
   LEFT JOIN emails e ON e.id = f.email_id;

ALTER TABLE tactile_accounts RENAME company_id TO organisation_id;
ALTER TABLE user_company_access RENAME company_id TO organisation_id;

ALTER TABLE tag_map RENAME company_id TO organisation_id;

DROP VIEW user_company_accessoverview;
CREATE VIEW user_company_access_overview AS
SELECT u.username, u.organisation_id, u.id, u.enabled, org.name AS organisation
   FROM user_company_access u
   LEFT JOIN organisations org ON u.organisation_id = org.id;

ALTER TABLE system_companies RENAME COLUMN company_id TO organisation_id;

DROP VIEW organisation_roles_overview;

DROP TABLE companypermissions CASCADE;
DROP TABLE haspermission;
DROP TABLE permissions;

ALTER TABLE company_id_seq RENAME TO organisations_id_seq;
ALTER TABLE person_id_seq RENAME TO people_id_seq;
ALTER TABLE company_contact_methods_id_seq RENAME TO organisation_contact_methods_id_seq; 
ALTER TABLE companyroles_id_seq RENAME TO organisation_roles_id_seq; 

-- fix tags!
UPDATE tag_map SET hash = 'peo'||substring(hash from '[0-9]+$') where hash like 'p%';
UPDATE tag_map SET hash = 'opp'||substring(hash from '[0-9]+$') where hash like 'o%';
UPDATE tag_map SET hash = 'act'||substring(hash from '[0-9]+$') where hash like 'a%';
UPDATE tag_map SET hash = 'org'||substring(hash from '[0-9]+$') where hash like 'c%';


--
-- countries_update_1203.sql
--

UPDATE countries SET name = 'Afghanistan' WHERE code = 'AF';
UPDATE countries SET name = 'Albania' WHERE code = 'AL';
UPDATE countries SET name = 'Algeria' WHERE code = 'DZ';
UPDATE countries SET name = 'American Samoa' WHERE code = 'AS';
UPDATE countries SET name = 'Andorra' WHERE code = 'AD';
UPDATE countries SET name = 'Angola' WHERE code = 'AO';
UPDATE countries SET name = 'Anguilla' WHERE code = 'AI';
UPDATE countries SET name = 'Antarctica' WHERE code = 'AQ';
UPDATE countries SET name = 'Antigua and Barbuda' WHERE code = 'AG';
UPDATE countries SET name = 'Argentina' WHERE code = 'AR';
UPDATE countries SET name = 'Armenia' WHERE code = 'AM';
UPDATE countries SET name = 'Aruba' WHERE code = 'AW';
UPDATE countries SET name = 'Australia' WHERE code = 'AU';
UPDATE countries SET name = 'Austria' WHERE code = 'AT';
UPDATE countries SET name = 'Azerbaijan' WHERE code = 'AZ';
UPDATE countries SET name = 'Bahamas' WHERE code = 'BS';
UPDATE countries SET name = 'Bahrain' WHERE code = 'BH';
UPDATE countries SET name = 'Bangladesh' WHERE code = 'BD';
UPDATE countries SET name = 'Barbados' WHERE code = 'BB';
UPDATE countries SET name = 'Belarus' WHERE code = 'BY';
UPDATE countries SET name = 'Belgium' WHERE code = 'BE';
UPDATE countries SET name = 'Belize' WHERE code = 'BZ';
UPDATE countries SET name = 'Benin' WHERE code = 'BJ';
UPDATE countries SET name = 'Bermuda' WHERE code = 'BM';
UPDATE countries SET name = 'Bhutan' WHERE code = 'BT';
UPDATE countries SET name = 'Bolivia' WHERE code = 'BO';
UPDATE countries SET name = 'Bosnia and Herzegovina' WHERE code = 'BA';
UPDATE countries SET name = 'Botswana' WHERE code = 'BW';
UPDATE countries SET name = 'Bouvet Island' WHERE code = 'BV';
UPDATE countries SET name = 'Brazil' WHERE code = 'BR';
UPDATE countries SET name = 'British Indian Ocean Territory' WHERE code = 'IO';
UPDATE countries SET name = 'Brunei Darussalam' WHERE code = 'BN';
UPDATE countries SET name = 'Bulgaria' WHERE code = 'BG';
UPDATE countries SET name = 'Burkina Faso' WHERE code = 'BF';
UPDATE countries SET name = 'Burundi' WHERE code = 'BI';
UPDATE countries SET name = 'Cambodia' WHERE code = 'KH';
UPDATE countries SET name = 'Cameroon' WHERE code = 'CM';
UPDATE countries SET name = 'Canada' WHERE code = 'CA';
UPDATE countries SET name = 'Cape Verde' WHERE code = 'CV';
UPDATE countries SET name = 'Cayman Islands' WHERE code = 'KY';
UPDATE countries SET name = 'Central African Republic' WHERE code = 'CF';
UPDATE countries SET name = 'Chad' WHERE code = 'TD';
UPDATE countries SET name = 'Chile' WHERE code = 'CL';
UPDATE countries SET name = 'China' WHERE code = 'CN';
UPDATE countries SET name = 'Christmas Island' WHERE code = 'CX';
UPDATE countries SET name = 'Cocos Keeling) Islands' WHERE code = 'CC';
UPDATE countries SET name = 'Colombia' WHERE code = 'CO';
UPDATE countries SET name = 'Comoros' WHERE code = 'KM';
UPDATE countries SET name = 'Congo' WHERE code = 'CG';
UPDATE countries SET name = 'Congo, Democratic Republic of the' WHERE code = 'CD';
UPDATE countries SET name = 'Cook Islands' WHERE code = 'CK';
UPDATE countries SET name = 'Costa Rica' WHERE code = 'CR';
UPDATE countries SET name = 'Cote d''Ivoire  Côte d''Ivoire' WHERE code = 'CI';
UPDATE countries SET name = 'Croatia' WHERE code = 'HR';
UPDATE countries SET name = 'Cuba' WHERE code = 'CU';
UPDATE countries SET name = 'Cyprus' WHERE code = 'CY';
UPDATE countries SET name = 'Czech Republic' WHERE code = 'CZ';
UPDATE countries SET name = 'Denmark' WHERE code = 'DK';
UPDATE countries SET name = 'Djibouti' WHERE code = 'DJ';
UPDATE countries SET name = 'Dominica' WHERE code = 'DM';
UPDATE countries SET name = 'Dominican Republic' WHERE code = 'DO';
UPDATE countries SET name = 'Ecuador' WHERE code = 'EC';
UPDATE countries SET name = 'Egypt' WHERE code = 'EG';
UPDATE countries SET name = 'El Salvador' WHERE code = 'SV';
UPDATE countries SET name = 'Equatorial Guinea' WHERE code = 'GQ';
UPDATE countries SET name = 'Eritrea' WHERE code = 'ER';
UPDATE countries SET name = 'Estonia' WHERE code = 'EE';
UPDATE countries SET name = 'Ethiopia' WHERE code = 'ET';
UPDATE countries SET name = 'Falkland Islands Malvinas)' WHERE code = 'FK';
UPDATE countries SET name = 'Faroe Islands' WHERE code = 'FO';
UPDATE countries SET name = 'Fiji' WHERE code = 'FJ';
UPDATE countries SET name = 'Finland' WHERE code = 'FI';
UPDATE countries SET name = 'France' WHERE code = 'FR';
UPDATE countries SET name = 'French Guiana' WHERE code = 'GF';
UPDATE countries SET name = 'French Polynesia' WHERE code = 'PF';
UPDATE countries SET name = 'French Southern Territories' WHERE code = 'TF';
UPDATE countries SET name = 'Gabon' WHERE code = 'GA';
UPDATE countries SET name = 'Gambia' WHERE code = 'GM';
UPDATE countries SET name = 'Georgia' WHERE code = 'GE';
UPDATE countries SET name = 'Germany' WHERE code = 'DE';
UPDATE countries SET name = 'Ghana' WHERE code = 'GH';
UPDATE countries SET name = 'Gibraltar' WHERE code = 'GI';
UPDATE countries SET name = 'Greece' WHERE code = 'GR';
UPDATE countries SET name = 'Greenland' WHERE code = 'GL';
UPDATE countries SET name = 'Grenada' WHERE code = 'GD';
UPDATE countries SET name = 'Guadeloupe' WHERE code = 'GP';
UPDATE countries SET name = 'Guam' WHERE code = 'GU';
UPDATE countries SET name = 'Guatemala' WHERE code = 'GT';
UPDATE countries SET name = 'Guinea' WHERE code = 'GN';
UPDATE countries SET name = 'Guinea-Bissau' WHERE code = 'GW';
UPDATE countries SET name = 'Guyana' WHERE code = 'GY';
UPDATE countries SET name = 'Haiti' WHERE code = 'HT';
UPDATE countries SET name = 'Heard Island and McDonald Islands' WHERE code = 'HM';
UPDATE countries SET name = 'Holy See Vatican City State)' WHERE code = 'VA';
UPDATE countries SET name = 'Honduras' WHERE code = 'HN';
UPDATE countries SET name = 'Hong Kong' WHERE code = 'HK';
UPDATE countries SET name = 'Hungary' WHERE code = 'HU';
UPDATE countries SET name = 'Iceland' WHERE code = 'IS';
UPDATE countries SET name = 'India' WHERE code = 'IN';
UPDATE countries SET name = 'Indonesia' WHERE code = 'ID';
UPDATE countries SET name = 'Iran, Islamic Republic of' WHERE code = 'IR';
UPDATE countries SET name = 'Iraq' WHERE code = 'IQ';
UPDATE countries SET name = 'Ireland' WHERE code = 'IE';
UPDATE countries SET name = 'Israel' WHERE code = 'IL';
UPDATE countries SET name = 'Italy' WHERE code = 'IT';
UPDATE countries SET name = 'Jamaica' WHERE code = 'JM';
UPDATE countries SET name = 'Japan' WHERE code = 'JP';
UPDATE countries SET name = 'Jordan' WHERE code = 'JO';
UPDATE countries SET name = 'Kazakhstan' WHERE code = 'KZ';
UPDATE countries SET name = 'Kenya' WHERE code = 'KE';
UPDATE countries SET name = 'Kiribati' WHERE code = 'KI';
UPDATE countries SET name = 'Korea, Democratic People''s Republic of' WHERE code = 'KP';
UPDATE countries SET name = 'Korea, Republic of' WHERE code = 'KR';
UPDATE countries SET name = 'Kuwait' WHERE code = 'KW';
UPDATE countries SET name = 'Kyrgyzstan' WHERE code = 'KG';
UPDATE countries SET name = 'Lao People''s Democratic Republic' WHERE code = 'LA';
UPDATE countries SET name = 'Latvia' WHERE code = 'LV';
UPDATE countries SET name = 'Lebanon' WHERE code = 'LB';
UPDATE countries SET name = 'Lesotho' WHERE code = 'LS';
UPDATE countries SET name = 'Liberia' WHERE code = 'LR';
UPDATE countries SET name = 'Libyan Arab Jamahiriya' WHERE code = 'LY';
UPDATE countries SET name = 'Liechtenstein' WHERE code = 'LI';
UPDATE countries SET name = 'Lithuania' WHERE code = 'LT';
UPDATE countries SET name = 'Luxembourg' WHERE code = 'LU';
UPDATE countries SET name = 'Macao' WHERE code = 'MO';
UPDATE countries SET name = 'Macedonia, the former Yugoslav Republic of' WHERE code = 'MK';
UPDATE countries SET name = 'Madagascar' WHERE code = 'MG';
UPDATE countries SET name = 'Malawi' WHERE code = 'MW';
UPDATE countries SET name = 'Malaysia' WHERE code = 'MY';
UPDATE countries SET name = 'Maldives' WHERE code = 'MV';
UPDATE countries SET name = 'Mali' WHERE code = 'ML';
UPDATE countries SET name = 'Malta' WHERE code = 'MT';
UPDATE countries SET name = 'Marshall Islands' WHERE code = 'MH';
UPDATE countries SET name = 'Martinique' WHERE code = 'MQ';
UPDATE countries SET name = 'Mauritania' WHERE code = 'MR';
UPDATE countries SET name = 'Mauritius' WHERE code = 'MU';
UPDATE countries SET name = 'Mayotte' WHERE code = 'YT';
UPDATE countries SET name = 'Mexico' WHERE code = 'MX';
UPDATE countries SET name = 'Micronesia, Federated States of' WHERE code = 'FM';
UPDATE countries SET name = 'Moldova, Republic of' WHERE code = 'MD';
UPDATE countries SET name = 'Monaco' WHERE code = 'MC';
UPDATE countries SET name = 'Mongolia' WHERE code = 'MN';
UPDATE countries SET name = 'Montserrat' WHERE code = 'MS';
UPDATE countries SET name = 'Morocco' WHERE code = 'MA';
UPDATE countries SET name = 'Mozambique' WHERE code = 'MZ';
UPDATE countries SET name = 'Myanmar' WHERE code = 'MM';
UPDATE countries SET name = 'Namibia' WHERE code = 'NA';
UPDATE countries SET name = 'Nauru' WHERE code = 'NR';
UPDATE countries SET name = 'Nepal' WHERE code = 'NP';
UPDATE countries SET name = 'Netherlands' WHERE code = 'NL';
UPDATE countries SET name = 'Netherlands Antilles' WHERE code = 'AN';
UPDATE countries SET name = 'New Caledonia' WHERE code = 'NC';
UPDATE countries SET name = 'New Zealand' WHERE code = 'NZ';
UPDATE countries SET name = 'Nicaragua' WHERE code = 'NI';
UPDATE countries SET name = 'Niger' WHERE code = 'NE';
UPDATE countries SET name = 'Nigeria' WHERE code = 'NG';
UPDATE countries SET name = 'Niue' WHERE code = 'NU';
UPDATE countries SET name = 'Norfolk Island' WHERE code = 'NF';
UPDATE countries SET name = 'Northern Mariana Islands' WHERE code = 'MP';
UPDATE countries SET name = 'Norway' WHERE code = 'NO';
UPDATE countries SET name = 'Oman' WHERE code = 'OM';
UPDATE countries SET name = 'Pakistan' WHERE code = 'PK';
UPDATE countries SET name = 'Palau' WHERE code = 'PW';
UPDATE countries SET name = 'Palestinian Territory, Occupied' WHERE code = 'PS';
UPDATE countries SET name = 'Panama' WHERE code = 'PA';
UPDATE countries SET name = 'Papua New Guinea' WHERE code = 'PG';
UPDATE countries SET name = 'Paraguay' WHERE code = 'PY';
UPDATE countries SET name = 'Peru' WHERE code = 'PE';
UPDATE countries SET name = 'Philippines' WHERE code = 'PH';
UPDATE countries SET name = 'Pitcairn' WHERE code = 'PN';
UPDATE countries SET name = 'Poland' WHERE code = 'PL';
UPDATE countries SET name = 'Portugal' WHERE code = 'PT';
UPDATE countries SET name = 'Puerto Rico' WHERE code = 'PR';
UPDATE countries SET name = 'Qatar' WHERE code = 'QA';
UPDATE countries SET name = 'Reunion  Réunion' WHERE code = 'RE';
UPDATE countries SET name = 'Romania' WHERE code = 'RO';
UPDATE countries SET name = 'Russian Federation' WHERE code = 'RU';
UPDATE countries SET name = 'Rwanda' WHERE code = 'RW';
UPDATE countries SET name = 'Saint Helena' WHERE code = 'SH';
UPDATE countries SET name = 'Saint Kitts and Nevis' WHERE code = 'KN';
UPDATE countries SET name = 'Saint Lucia' WHERE code = 'LC';
UPDATE countries SET name = 'Saint Pierre and Miquelon' WHERE code = 'PM';
UPDATE countries SET name = 'Saint Vincent and the Grenadines' WHERE code = 'VC';
UPDATE countries SET name = 'Samoa' WHERE code = 'WS';
UPDATE countries SET name = 'San Marino' WHERE code = 'SM';
UPDATE countries SET name = 'Sao Tome and Principe' WHERE code = 'ST';
UPDATE countries SET name = 'Saudi Arabia' WHERE code = 'SA';
UPDATE countries SET name = 'Senegal' WHERE code = 'SN';
UPDATE countries SET name = 'Seychelles' WHERE code = 'SC';
UPDATE countries SET name = 'Sierra Leone' WHERE code = 'SL';
UPDATE countries SET name = 'Singapore' WHERE code = 'SG';
UPDATE countries SET name = 'Slovakia' WHERE code = 'SK';
UPDATE countries SET name = 'Slovenia' WHERE code = 'SI';
UPDATE countries SET name = 'Solomon Islands' WHERE code = 'SB';
UPDATE countries SET name = 'Somalia' WHERE code = 'SO';
UPDATE countries SET name = 'South Africa' WHERE code = 'ZA';
UPDATE countries SET name = 'South Georgia and the South Sandwich Islands' WHERE code = 'GS';
UPDATE countries SET name = 'Spain' WHERE code = 'ES';
UPDATE countries SET name = 'Sri Lanka' WHERE code = 'LK';
UPDATE countries SET name = 'Sudan' WHERE code = 'SD';
UPDATE countries SET name = 'Suriname' WHERE code = 'SR';
UPDATE countries SET name = 'Svalbard and Jan Mayen' WHERE code = 'SJ';
UPDATE countries SET name = 'Swaziland' WHERE code = 'SZ';
UPDATE countries SET name = 'Sweden' WHERE code = 'SE';
UPDATE countries SET name = 'Switzerland' WHERE code = 'CH';
UPDATE countries SET name = 'Syrian Arab Republic' WHERE code = 'SY';
UPDATE countries SET name = 'Taiwan, Province of China' WHERE code = 'TW';
UPDATE countries SET name = 'Tajikistan' WHERE code = 'TJ';
UPDATE countries SET name = 'Tanzania, United Republic of' WHERE code = 'TZ';
UPDATE countries SET name = 'Thailand' WHERE code = 'TH';
UPDATE countries SET name = 'Timor-Leste' WHERE code = 'TL';
UPDATE countries SET name = 'Togo' WHERE code = 'TG';
UPDATE countries SET name = 'Tokelau' WHERE code = 'TK';
UPDATE countries SET name = 'Tonga' WHERE code = 'TO';
UPDATE countries SET name = 'Trinidad and Tobago' WHERE code = 'TT';
UPDATE countries SET name = 'Tunisia' WHERE code = 'TN';
UPDATE countries SET name = 'Turkey' WHERE code = 'TR';
UPDATE countries SET name = 'Turkmenistan' WHERE code = 'TM';
UPDATE countries SET name = 'Turks and Caicos Islands' WHERE code = 'TC';
UPDATE countries SET name = 'Tuvalu' WHERE code = 'TV';
UPDATE countries SET name = 'Uganda' WHERE code = 'UG';
UPDATE countries SET name = 'Ukraine' WHERE code = 'UA';
UPDATE countries SET name = 'United Arab Emirates' WHERE code = 'AE';
UPDATE countries SET name = 'United Kingdom' WHERE code = 'GB';
UPDATE countries SET name = 'United States' WHERE code = 'US';
UPDATE countries SET name = 'United States Minor Outlying Islands' WHERE code = 'UM';
UPDATE countries SET name = 'Uruguay' WHERE code = 'UY';
UPDATE countries SET name = 'Uzbekistan' WHERE code = 'UZ';
UPDATE countries SET name = 'Vanuatu' WHERE code = 'VU';
UPDATE countries SET name = 'Venezuela, Bolivarian Republic of' WHERE code = 'VE';
UPDATE countries SET name = 'Viet Nam' WHERE code = 'VN';
UPDATE countries SET name = 'Virgin Islands, British' WHERE code = 'VG';
UPDATE countries SET name = 'Virgin Islands, U.S.' WHERE code = 'VI';
UPDATE countries SET name = 'Wallis and Futuna' WHERE code = 'WF';
UPDATE countries SET name = 'Western Sahara' WHERE code = 'EH';
UPDATE countries SET name = 'Yemen' WHERE code = 'YE';
UPDATE countries SET name = 'Zambia' WHERE code = 'ZM';
UPDATE countries SET name = 'Zimbabwe' WHERE code = 'ZW';
DELETE FROM countries WHERE code = 'YU';

INSERT INTO countries (name, code) VALUES ('Aland Islands  Åland Islands','AX');
INSERT INTO countries (name, code) VALUES ('Guernsey','GG');
INSERT INTO countries (name, code) VALUES ('Isle of Man','IM');
INSERT INTO countries (name, code) VALUES ('Jersey','JE');
INSERT INTO countries (name, code) VALUES ('Montenegro','ME');
INSERT INTO countries (name, code) VALUES ('Saint Barthélemy','BL');
INSERT INTO countries (name, code) VALUES ('Saint Martin French part)','MF');
INSERT INTO countries (name, code) VALUES ('Serbia','RS');

-- add_invoicing_1242.sql
-- ' Fixing my syntax highlighting!

ALTER TABLE tactile_accounts ADD COLUMN country varchar REFERENCES countries(code);
ALTER TABLE tactile_accounts ADD COLUMN vat_number integer;

ALTER TABLE tactile_accounts ADD COLUMN vat_number_new varchar;
UPDATE tactile_accounts SET vat_number_new = vat_number;
ALTER TABLE tactile_accounts DROP COLUMN vat_number;
ALTER TABLE tactile_accounts RENAME vat_number_new TO vat_number;


UPDATE tactile_accounts ta SET country = c.code FROM countries c WHERE c.name = ta.country;

CREATE TABLE invoices (
  id bigserial NOT NULL,
  account_id integer NOT NULL REFERENCES tactile_accounts(id),
  invoice_date date NOT NULL,
  created timestamp NOT NULL DEFAULT NOW(),
  vat_rate numeric NOT NULL,
  sent_at timestamp,
  PRIMARY KEY (id)
);

CREATE TABLE invoice_lines (
  id bigserial NOT NULL,
  invoice_id bigint NOT NULL REFERENCES invoices(id),
  payment_record_id bigint NOT NULL REFERENCES payment_records(id),
  product varchar NOT NULL DEFAULT 'Tactile',
  net_amount numeric NOT NULL,
  gross_amount numeric NOT NULL,
  plan_id integer NOT NULL REFERENCES account_plans(id),
  created timestamp NOT NULL DEFAULT NOW(),
  PRIMARY KEY (id)
);


ALTER TABLE payment_records ADD COLUMN invoiced boolean NOT NULL DEFAULT 'f';

--
-- fixing email overview
--

DROP VIEW s3_files_overview;
CREATE VIEW s3_files_overview AS
SELECT f.id, f.bucket, f.object, f.filename, f.content_type, f.size, f.extension, f.comment, f.owner, f.created, f.usercompanyid, f.organisation_id, org.name AS organisation, f.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person, f.opportunity_id, o.name AS opportunity, f.activity_id, a.name AS activity, f.email_id, e.subject AS email, f.ticket_id, f.changeset_id
   FROM s3_files f
   LEFT JOIN organisations org ON org.id = f.organisation_id
   LEFT JOIN people p ON p.id = f.person_id
   LEFT JOIN opportunities o ON o.id = f.opportunity_id
   LEFT JOIN tactile_activities a ON a.id = f.activity_id
   LEFT JOIN emails e ON e.id = f.email_id;

--
-- fixing_resolve.sql
--

ALTER TABLE tickets RENAME COLUMN company_id TO organisation_id;

--
-- entanet.sql
--

ALTER TABLE tactile_accounts ADD entanet_domain varchar;
ALTER TABLE tactile_accounts ADD entanet_code varchar;

CREATE TABLE entanet_extensions (
	username varchar not null references users(username) on update cascade on delete cascade,
	extension varchar not null,
	usercompanyid bigint not null references organisations(id) on update cascade on delete cascade
);

create index person_contact_methods_contact_normalize on person_contact_methods(replace(contact,' ',''));
create index company_contact_methods_contact_normalize on organisation_contact_methods(replace(contact,' ',''));

--
-- colours.sql
--

ALTER TABLE tactile_accounts ADD COLUMN theme VARCHAR NOT NULL DEFAULT 'green';
ALTER TABLE organisations ADD COLUMN logo_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- people_portraits.sql
--

ALTER TABLE people ADD COLUMN logo_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE people ADD COLUMN thumbnail_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE organisations ADD COLUMN thumbnail_id INT REFERENCES s3_files(id) ON DELETE SET NULL ON UPDATE CASCADE;


--
-- peruser_pricing.sql
--

ALTER TABLE account_plans ADD COLUMN per_user BOOLEAN NOT NULL DEFAULT FALSE;
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month, per_user)
VALUES ('Solo', '1', '10485760', '20', '250', '0', 'true');
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month, per_user)
VALUES ('Premium', '0', '104857600', '0', '0', '6', 'true');
ALTER TABLE tactile_accounts ADD COLUMN per_user_limit INT NOT NULL DEFAULT '1';
ALTER TABLE payment_records ADD COLUMN description VARCHAR;
ALTER TABLE payment_records ADD COLUMN repeatable BOOLEAN NOT NULL DEFAULT TRUE;


--
-- campaignmonitor.sql
--

ALTER TABLE tactile_accounts ADD COLUMN cm_key VARCHAR;
ALTER TABLE tactile_accounts ADD COLUMN cm_client_id VARCHAR;
ALTER TABLE tactile_accounts ADD COLUMN cm_client VARCHAR;


--
-- flags.sql
--

CREATE TABLE flags (
id SERIAL PRIMARY KEY,
person_id INT REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
organisation_id INT REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
opportunity_id INT REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE,
activity_id INT REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE,
title VARCHAR NOT NULL,
created TIMESTAMP NOT NULL DEFAULT now(),
owner VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE VIEW flags_overview AS
SELECT f.id, f.person_id, f.organisation_id, f.opportunity_id, f.activity_id, f.title, f.created, f.owner, f.usercompanyid,
org.name as organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, opp.name as opportunity, act.name as activity
FROM flags f
LEFT JOIN people p ON f.person_id = p.id
LEFT JOIN organisations org ON f.organisation_id = org.id
LEFT JOIN opportunities opp ON f.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON f.activity_id = act.id;


--
-- motds.sql
--

CREATE TABLE motds(
id SERIAL PRIMARY KEY,
message_start TIMESTAMP NOT NULL DEFAULT now(),
message_end TIMESTAMP,
active BOOLEAN NOT NULL DEFAULT FALSE,
important BOOLEAN NOT NULL DEFAULT FALSE,
content TEXT NOT NULL
);


--
-- search.sql
--

CREATE VIEW search_overview AS
SELECT usercompanyid, 'organisations' AS type, org.id, name, assigned_to, owner, org.id as organisation_id, orghr.username, false as private
	FROM organisations org
	LEFT JOIN organisation_roles orgr ON org.id = orgr.organisation_id AND orgr.read
	LEFT JOIN hasrole orghr ON orgr.roleid = orghr.roleid
UNION SELECT usercompanyid, 'people' AS type, p.id, firstname || ' ' || surname as name, assigned_to, owner, p.organisation_id, phr.username, private
	FROM people p
	LEFT JOIN organisation_roles pr ON p.organisation_id = pr.organisation_id AND pr.read
	LEFT JOIN hasrole phr ON pr.roleid = phr.roleid
UNION SELECT usercompanyid, 'opportunities' AS type, opp.id, name, assigned_to, owner, opp.organisation_id, opphr.username, false as private
	FROM opportunities opp
	LEFT JOIN organisation_roles oppr ON opp.organisation_id = oppr.organisation_id AND oppr.read
	LEFT JOIN hasrole opphr ON oppr.roleid = opphr.roleid
UNION SELECT usercompanyid, 'activities' AS type, act.id, name, assigned_to, owner, act.organisation_id, acthr.username, false as private
	FROM tactile_activities act
	LEFT JOIN organisation_roles actr ON act.organisation_id = actr.organisation_id AND actr.read
	LEFT JOIN hasrole acthr ON actr.roleid = acthr.roleid
;


--
-- contact_method_revamp.sql
--

CREATE TABLE contact_method_order (
	type varchar(1) not null,
	position int not null default 999
);
INSERT INTO contact_method_order (type, position) VALUES ('T', 1);
INSERT INTO contact_method_order (type, position) VALUES ('E', 2);
INSERT INTO contact_method_order (type, position) VALUES ('M', 3);
INSERT INTO contact_method_order (type, position) VALUES ('W', 4);
INSERT INTO contact_method_order (type, position) VALUES ('F', 5);
INSERT INTO contact_method_order (type, position) VALUES ('R', 6);
INSERT INTO contact_method_order (type, position) VALUES ('S', 7);
INSERT INTO contact_method_order (type, position) VALUES ('L', 8);
INSERT INTO contact_method_order (type, position) VALUES ('I', 9);

INSERT INTO organisation_contact_methods (organisation_id, contact, type, main, name)
SELECT id as organisation_id, website as contact, 'W' as type, true as main, 'Main' as name FROM organisations
WHERE website IS NOT NULL;
UPDATE organisation_contact_methods SET contact = regexp_replace(contact, 'http://', '') WHERE type = 'W';
ALTER TABLE organisations DROP COLUMN website;

CREATE VIEW organisation_contact_methods_overview AS
SELECT c.id, c.organisation_id, c.type, c.main, c.contact, c.name, o.position FROM organisation_contact_methods c
JOIN contact_method_order o ON c.type = o.type;

CREATE VIEW person_contact_methods_overview AS
SELECT c.id, c.person_id, c.type, c.main, c.contact, c.name, o.position FROM person_contact_methods c
JOIN contact_method_order o ON c.type = o.type;


--
-- default_country.sql 
--

ALTER TABLE tactile_accounts RENAME COLUMN country TO country_code;


--
-- email-sending.sql
--

CREATE TABLE email_templates (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	name VARCHAR NOT NULL,
	subject VARCHAR NOT NULL,
	body TEXT NOT NULL,
	enabled BOOLEAN NOT NULL DEFAULT TRUE,
	UNIQUE (usercompanyid, name)
);

CREATE TABLE tactile_email_addresses (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	role_id INT NOT NULL REFERENCES roles(id) ON UPDATE CASCADE ON DELETE CASCADE,
	email_address VARCHAR NOT NULL,
	display_name VARCHAR,
	verify_code VARCHAR,
	verified_at TIMESTAMP,
	send BOOLEAN NOT NULL DEFAULT FALSE,
	UNIQUE (usercompanyid, role_id, email_address)
);

CREATE VIEW tactile_email_addresses_overview AS
SELECT ea.id, ea.usercompanyid, ea.created, ea.role_id, r.name AS role, ea.email_address, ea.display_name, ea.verify_code, ea.verified_at, (ea.verified_at IS NOT NULL) as verified, ea.send
FROM tactile_email_addresses ea
JOIN roles r ON r.id = role_id; 

CREATE TABLE tactile_accounts_magic (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	key VARCHAR NOT NULL,
	value VARCHAR NOT NULL,
	UNIQUE (usercompanyid, key)
);
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'theme', theme FROM tactile_accounts WHERE theme IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'freshbooks_account', freshbooks_account FROM tactile_accounts WHERE freshbooks_account IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'freshbooks_token', freshbooks_token FROM tactile_accounts WHERE freshbooks_token IS NOT NULL AND organisation_id IS NOT NULL;
--INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_siteaddress', zendesk_siteaddress FROM tactile_accounts WHERE zendesk_siteaddress IS NOT NULL AND organisation_id IS NOT NULL;
--INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_email', zendesk_email FROM tactile_accounts WHERE zendesk_email IS NOT NULL AND organisation_id IS NOT NULL;
--INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'zendesk_password', zendesk_password FROM tactile_accounts WHERE zendesk_password IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_key', cm_key FROM tactile_accounts WHERE cm_key IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_client_id', cm_client_id FROM tactile_accounts WHERE cm_client_id IS NOT NULL AND organisation_id IS NOT NULL;
INSERT INTO tactile_accounts_magic (usercompanyid, key, value) SELECT organisation_id, 'cm_client', cm_client FROM tactile_accounts WHERE cm_client IS NOT NULL AND organisation_id IS NOT NULL;
ALTER TABLE tactile_accounts DROP COLUMN theme;
ALTER TABLE tactile_accounts DROP COLUMN freshbooks_account;
ALTER TABLE tactile_accounts DROP COLUMN freshbooks_token;
--ALTER TABLE tactile_accounts DROP COLUMN zendesk_siteaddress;
--ALTER TABLE tactile_accounts DROP COLUMN zendesk_email;
--ALTER TABLE tactile_accounts DROP COLUMN zendesk_password;
ALTER TABLE tactile_accounts DROP COLUMN cm_key;
ALTER TABLE tactile_accounts DROP COLUMN cm_client_id;
ALTER TABLE tactile_accounts DROP COLUMN cm_client;
UPDATE tactile_accounts_magic SET value = 'green' WHERE key = 'theme' AND value = '';

CREATE TABLE mail_queue_send (
	id SERIAL PRIMARY KEY,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	owner VARCHAR NOT NULL REFERENCES users(username),
	from_id INT NOT NULL REFERENCES tactile_email_addresses(id),
	to_address VARCHAR NOT NULL,
	subject VARCHAR NOT NULL,
	body TEXT NOT NULL,
	attempts INT NOT NULL DEFAULT 1
);


--
-- timeline.sql
--

ALTER TABLE emails ADD COLUMN activity_id INT REFERENCES tactile_activities(id);

CREATE VIEW timeline AS
SELECT
e.id, 'email' as type, e.usercompanyid, e.subject as title, e.body, e.received as when,
e.owner, null as assigned_to, false as private,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
ehr.username
FROM emails e
LEFT JOIN organisation_roles eor on e.organisation_id = eor.organisation_id AND eor.read
LEFT JOIN hasrole ehr ON eor.roleid = ehr.roleid

UNION SELECT
n.id, 'note' as type, n.usercompanyid, n.title, n.note as body, n.lastupdated as when,
n.owner, null as assigned_to, n.private,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
nhr.username
FROM notes n
LEFT JOIN organisation_roles nor on n.organisation_id = nor.organisation_id AND nor.read
LEFT JOIN hasrole nhr ON nor.roleid = nhr.roleid
WHERE NOT n.deleted

UNION SELECT
f.id, 'flag' as type, f.usercompanyid, f.title, '' as body, f.created as when,
f.owner, null as assigned_to, false as private,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
fhr.username
FROM flags f
LEFT JOIN organisation_roles foroles on f.organisation_id = foroles.organisation_id AND foroles.read
LEFT JOIN hasrole fhr ON foroles.roleid = fhr.roleid
;


--
-- timeline_restricted.sql
--

DROP VIEW timeline;
CREATE VIEW timeline AS
SELECT
n.id, 'note' as type, n.usercompanyid, n.lastupdated as when,
n.owner, n.owner as assigned_to, n.private,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id
FROM notes n
WHERE NOT n.deleted

UNION ALL SELECT
e.id, 'email' as type, e.usercompanyid, e.received as when,
e.owner, e.owner as assigned_to, false as private,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id
FROM emails e

UNION ALL SELECT
f.id, 'flag' as type, f.usercompanyid, f.created as when,
f.owner, f.owner as assigned_to, false as private,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id
FROM flags f

UNION ALL SELECT
s3.id, 's3file' as type, s3.usercompanyid, s3.created as when,
s3.owner, s3.owner as assigned_to, false as private,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id
FROM s3_files s3
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'

UNION ALL SELECT
o.id, 'opportunity' as type, o.usercompanyid, o.created as when,
o.owner, o.owner as assigned_to, false as private,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id
FROM opportunities o
WHERE o.archived = FALSE

UNION ALL SELECT
na.id, 'new_activity' as type, na.usercompanyid, na.created as when,
na.owner, na.assigned_to as assigned_to, false as private,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id
FROM tactile_activities_overview na
WHERE na.completed IS NULL AND NOT na.overdue

UNION ALL SELECT
ca.id, 'completed_activity' as type, ca.usercompanyid, ca.completed as when,
ca.owner, ca.assigned_to as assigned_to, false as private,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id
FROM tactile_activities_overview ca
WHERE ca.completed IS NOT NULL

UNION ALL SELECT
oa.id, 'overdue_activity' as type, oa.usercompanyid, oa.due as when,
oa.owner, oa.assigned_to as assigned_to, false as private,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id
FROM tactile_activities_overview oa
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;

CREATE VIEW timeline_restricted AS
SELECT t.*, hr.username from timeline t
LEFT JOIN organisation_roles oroles on t.organisation_id = oroles.organisation_id AND oroles.read
LEFT JOIN hasrole hr ON oroles.roleid = hr.roleid
;


--
-- timeline_full.sql
--

DROP VIEW timeline CASCADE;
CREATE VIEW timeline AS
SELECT
n.id, 'note' as type, n.usercompanyid, n.lastupdated as when,
n.title, n.note as body,
'' as email_from, '' as email_to,
n.created, n.lastupdated, n.lastupdated as received, n.lastupdated as due, n.lastupdated as completed,
n.private, false as overdue, null as direction,
n.owner, n.owner as assigned_to, n.owner as assigned_by,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id
FROM notes n
WHERE NOT n.deleted

UNION ALL SELECT
e.id, 'email' as type, e.usercompanyid, e.received as when,
e.subject as title, e.body,
e.email_from, e.email_to,
e.created, e.created as lastupdated, e.received, e.received as due, e.received as completed,
false as private, false as overdue, 'outgoing' as direction,
e.owner, e.owner as assigned_to, e.owner as assigned_by,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id
FROM emails e

UNION ALL SELECT
f.id, 'flag' as type, f.usercompanyid, f.created as when,
f.title, f.title as body,
'' as email_from, '' as email_to,
f.created, f.created as lastupdated, f.created as received, f.created as due, f.created as completed,
false as private, false as overdue, null as direction,
f.owner, f.owner as assigned_to, f.owner as assigned_by,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id
FROM flags f

UNION ALL SELECT
s3.id, 's3file' as type, s3.usercompanyid, s3.created as when,
s3.filename as title, s3.filename as body,
'' as email_from, '' as email_to,
s3.created, s3.created as lastupdated, s3.created as received, s3.created as due, s3.created as completed,
false as private, false as overdue, null as direction,
s3.owner, s3.owner as assigned_to, s3.owner as assigned_by,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id
FROM s3_files s3
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'

UNION ALL SELECT
o.id, 'opportunity' as type, o.usercompanyid, o.created as when,
o.name as title, o.description as body,
'' as email_from, '' as email_to,
o.created, o.created as lastupdated, o.created as received, o.created as due, o.created as completed,
false as private, false as overdue, null as direction,
o.owner, o.owner as assigned_to, o.owner as assigned_by,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id
FROM opportunities o
WHERE o.archived = FALSE

UNION ALL SELECT
na.id, 'new_activity' as type, na.usercompanyid, na.created as when,
na.name as title, na.description as body,
'' as email_from, '' as email_to,
na.created, na.created as lastupdated, na.created as received, na.due, na.completed,
false as private, na.overdue, null as direction,
na.owner, na.assigned_to as assigned_to, na.owner as assigned_by,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id
FROM tactile_activities_overview na
WHERE na.completed IS NULL AND NOT na.overdue

UNION ALL SELECT
ca.id, 'completed_activity' as type, ca.usercompanyid, ca.completed as when,
ca.name as title, ca.description as body,
'' as email_from, '' as email_to,
ca.created, ca.created as lastupdated, ca.created as received, ca.due, ca.completed,
false as private, ca.overdue, null as direction,
ca.owner, ca.assigned_to as assigned_to, ca.owner as assigned_by,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id
FROM tactile_activities_overview ca
WHERE ca.completed IS NOT NULL

UNION ALL SELECT
oa.id, 'overdue_activity' as type, oa.usercompanyid, oa.due as when,
oa.name as title, oa.description as body,
'' as email_from, '' as email_to,
oa.created, oa.created as lastupdated, oa.created as received, oa.due, oa.completed,
false as private, oa.overdue, null as direction,
oa.owner, oa.assigned_to as assigned_to, oa.owner as assigned_by,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id
FROM tactile_activities_overview oa
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;

CREATE VIEW timeline_full AS
SELECT t.*,
org.name as organisation, p.firstname || ' ' || p.surname as person, opp.name as opportunity, a.name as activity
FROM timeline t
LEFT JOIN organisations org ON org.id = t.organisation_id
LEFT JOIN people p ON p.id = t.person_id
LEFT JOIN opportunities opp ON opp.id = t.opportunity_id
LEFT JOIN tactile_activities a ON a.id = t.activity_id
;

CREATE VIEW timeline_restricted AS
SELECT t.*, hr.username from timeline_full t
LEFT JOIN organisation_roles oroles on t.organisation_id = oroles.organisation_id AND oroles.read
LEFT JOIN hasrole hr ON oroles.roleid = hr.roleid
;


--
-- timeline_segmented.sql
--

DROP VIEW timeline CASCADE;

CREATE VIEW timeline_notes AS
SELECT
n.id, 'note'::varchar(20) as type, n.usercompanyid, n.lastupdated as when,
n.title, n.note as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
n.created, n.lastupdated, n.lastupdated as received, n.lastupdated as due, n.lastupdated as completed,
n.private, false as overdue, null::varchar(20) as direction,
n.owner, n.owner as assigned_to, n.owner as assigned_by,
n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM notes n
LEFT JOIN people p ON n.person_id=p.id
LEFT JOIN organisations o ON n.organisation_id=o.id
LEFT JOIN opportunities opp on n.opportunity_id = opp.id
LEFT JOIN tactile_activities act on n.activity_id = act.id
WHERE NOT n.deleted
;

CREATE VIEW timeline_email_attachment_count AS
SELECT email_id, count(*) as count
FROM s3_files
WHERE email_id IS NOT NULL
GROUP BY email_id;

CREATE VIEW timeline_emails AS
SELECT DISTINCT
e.id, 'email'::varchar(20) as type, e.usercompanyid, e.received as when,
e.subject as title, e.body,
e.email_from, e.email_to,
ea.count as email_attachments,
e.created, e.created as lastupdated, e.received, e.received as due, e.received as completed,
false as private, false as overdue,
CASE
WHEN pcm.contact::text = e.email_from THEN 'outgoing'::varchar(20)
WHEN pcm.contact::text = e.email_to THEN 'incoming'::varchar(20)
ELSE ''::varchar(20)
END AS direction,
e.owner, e.owner as assigned_to, e.owner as assigned_by,
e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM emails e
LEFT JOIN people p ON e.person_id=p.id
LEFT JOIN timeline_email_attachment_count ea on e.id = ea.email_id
LEFT JOIN organisations o ON e.organisation_id=o.id
LEFT JOIN opportunities opp on e.opportunity_id = opp.id
LEFT JOIN tactile_activities act on e.activity_id = act.id
LEFT JOIN users u ON u.username::text = e.owner::text
LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from = pcm.contact OR e.email_to = pcm.contact)
;

CREATE VIEW timeline_flags AS
SELECT
f.id, 'flag'::varchar(20) as type, f.usercompanyid, f.created as when,
f.title, f.title as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
f.created, f.created as lastupdated, f.created as received, f.created as due, f.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
f.owner, f.owner as assigned_to, f.owner as assigned_by,
f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM flags f
LEFT JOIN people p ON f.person_id=p.id
LEFT JOIN organisations o ON f.organisation_id=o.id
LEFT JOIN opportunities opp on f.opportunity_id = opp.id
LEFT JOIN tactile_activities act on f.activity_id = act.id
;

CREATE VIEW timeline_s3_files AS
SELECT
s3.id, 's3file'::varchar(20) as type, s3.usercompanyid, s3.created as when,
s3.filename as title, s3.filename as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
s3.created, s3.created as lastupdated, s3.created as received, s3.created as due, s3.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
s3.owner, s3.owner as assigned_to, s3.owner as assigned_by,
s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id,
p.firstname||' '||p.surname as person,
o.name as organisation,
opp.name as opportunity,
act.name as activity
FROM s3_files s3
LEFT JOIN people p ON s3.person_id=p.id
LEFT JOIN organisations o ON s3.organisation_id=o.id
LEFT JOIN opportunities opp on s3.opportunity_id = opp.id
LEFT JOIN tactile_activities act on s3.activity_id = act.id
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket != 'tactile_public'
;

CREATE VIEW timeline_opportunities AS
SELECT
o.id, 'opportunity'::varchar(20) as type, o.usercompanyid, o.created as when,
o.name as title, o.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
o.created, o.created as lastupdated, o.created as received, o.created as due, o.created as completed,
false as private, false as overdue, null::varchar(20) as direction,
o.owner, o.owner as assigned_to, o.owner as assigned_by,
o.organisation_id, o.person_id, o.id as opportunity_id, o.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
''::varchar as opportunity,
''::varchar as activity
FROM opportunities o
LEFT JOIN people p ON o.person_id=p.id
LEFT JOIN organisations org ON o.organisation_id=org.id
WHERE o.archived = FALSE
;

CREATE VIEW timeline_activities_new AS 
SELECT
na.id, 'new_activity'::varchar(20) as type, na.usercompanyid, na.created as when,
na.name as title, na.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
na.created, na.created as lastupdated, na.created as received, na.due, na.completed,
false as private, na.overdue, null::varchar(20) as direction,
na.owner, na.assigned_to as assigned_to, na.owner as assigned_by,
na.organisation_id, na.person_id, na.opportunity_id, na.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview na
LEFT JOIN people p ON na.person_id=p.id
LEFT JOIN organisations org ON na.organisation_id=org.id
LEFT JOIN opportunities opp on na.opportunity_id = opp.id
WHERE na.completed IS NULL AND NOT na.overdue
;

CREATE VIEW timeline_activities_completed AS
SELECT
ca.id, 'completed_activity'::varchar(20) as type, ca.usercompanyid, ca.completed as when,
ca.name as title, ca.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
ca.created, ca.created as lastupdated, ca.created as received, ca.due, ca.completed,
false as private, ca.overdue, null::varchar(20) as direction,
ca.owner, ca.assigned_to as assigned_to, ca.owner as assigned_by,
ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview ca
LEFT JOIN people p ON ca.person_id=p.id
LEFT JOIN organisations org ON ca.organisation_id=org.id
LEFT JOIN opportunities opp on ca.opportunity_id = opp.id
WHERE ca.completed IS NOT NULL
;

CREATE VIEW timeline_activities_overdue AS
SELECT
oa.id, 'overdue_activity'::varchar(20) as type, oa.usercompanyid, oa.due as when,
oa.name as title, oa.description as body,
''::varchar as email_from, ''::varchar as email_to, 0::integer as email_attachments,
oa.created, oa.created as lastupdated, oa.created as received, oa.due, oa.completed,
false as private, oa.overdue, null::varchar(20) as direction,
oa.owner, oa.assigned_to as assigned_to, oa.owner as assigned_by,
oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id as activity_id,
p.firstname||' '||p.surname as person,
org.name as organisation,
opp.name as opportunity,
''::varchar as activity
FROM tactile_activities_overview oa
LEFT JOIN people p ON oa.person_id=p.id
LEFT JOIN organisations org ON oa.organisation_id=org.id
LEFT JOIN opportunities opp on oa.opportunity_id = opp.id
WHERE oa.completed IS NULL AND oa.overdue AND oa.class != 'event'
;
--
-- fixing useroverview (not the same in production?)
-- was missing dropboxkey
--

DROP VIEW useroverview;
CREATE VIEW useroverview AS
SELECT u.username, u.password, u.enabled, u.lastcompanylogin, u.person_id, u.dropboxkey, uca.organisation_id AS usercompanyid, (p.firstname::text || ' '::text) || p.surname::text AS person
FROM users u
LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
LEFT JOIN people p ON u.person_id = p.id;


-- custom_fields.sql

create table custom_fields(
id serial primary key,
usercompanyid bigint REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
name varchar not null,
type varchar(2) not null,
organisations boolean default false,
people boolean default false,
opportunities boolean default false,
activities boolean default false,
created timestamp not null default now()
);

create table custom_field_options(
id serial primary key,
field_id bigint not null REFERENCES custom_fields(id) ON UPDATE CASCADE ON DELETE CASCADE,
value text,
UNIQUE (field_id, value)
);

create table custom_field_map(
id serial primary key,
field_id bigint REFERENCES custom_fields(id) ON UPDATE CASCADE ON DELETE CASCADE,
organisation_id bigint REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
person_id bigint REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
opportunity_id bigint REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE,
activity_id bigint REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE,
hash varchar not null,
value text,
enabled boolean, 
option bigint REFERENCES custom_field_options(id) ON UPDATE CASCADE ON DELETE CASCADE,
UNIQUE (field_id, organisation_id),
UNIQUE (field_id, person_id),
UNIQUE (field_id, opportunity_id),
UNIQUE (field_id, activity_id)
);

CREATE VIEW custom_field_map_overview as
SELECT m.*, f.name, f.type, o.value as option_name
FROM custom_field_map m 
	LEFT JOIN custom_fields f ON m.field_id = f.id
	LEFT JOIN custom_field_options o ON m.option = o.id;

INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Employees'::varchar, 'n'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Employees') as field_id, id, 'org'||id::varchar as hash, employees FROM organisations o WHERE o.employees IS NOT NULL;
ALTER TABLE organisations DROP COLUMN employees;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Credit Limit'::varchar, 'n'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Credit Limit') as field_id, id, 'org'||id::varchar as hash, creditlimit FROM organisations o WHERE o.creditlimit IS NOT NULL;
ALTER TABLE organisations DROP COLUMN creditlimit;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'VAT Number'::varchar, 't'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'VAT Number') as field_id, id, 'org'||id::varchar as hash, vatnumber FROM organisations o WHERE o.vatnumber IS NOT NULL;
ALTER TABLE organisations DROP COLUMN vatnumber;
INSERT INTO custom_fields (usercompanyid, name, type, organisations) SELECT organisation_id, 'Company Number'::varchar, 't'::varchar, true FROM tactile_accounts;
INSERT INTO custom_field_map (field_id, organisation_id, hash, value) SELECT (SELECT cf.id FROM custom_fields cf WHERE cf.usercompanyid = o.usercompanyid and cf.name = 'Company Number') as field_id, id, 'org'||id::varchar as hash, companynumber FROM organisations o WHERE o.companynumber IS NOT NULL;
ALTER TABLE organisations DROP COLUMN companynumber;


-- multiple_addresses.sql

-- Create address tables and views
CREATE TABLE organisation_addresses (
	id SERIAL PRIMARY KEY,
	organisation_id INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	name VARCHAR NOT NULL DEFAULT 'Main',
	main BOOLEAN NOT NULL DEFAULT FALSE,
	street1 VARCHAR,
	street2 VARCHAR,
	street3 VARCHAR,
	town VARCHAR,
	county VARCHAR,
	postcode VARCHAR,
	country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE VIEW organisation_addresses_overview AS SELECT a.id, a.organisation_id, a.name, a.main, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, c.name AS country FROM organisation_addresses a LEFT JOIN countries c ON a.country_code = c.code;
CREATE TABLE person_addresses (
	id SERIAL PRIMARY KEY,
	person_id INT NOT NULL REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
	name VARCHAR NOT NULL DEFAULT 'Main',
	main BOOLEAN NOT NULL DEFAULT FALSE,
	street1 VARCHAR,
	street2 VARCHAR,
	street3 VARCHAR,
	town VARCHAR,
	county VARCHAR,
	postcode VARCHAR,
	country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE VIEW person_addresses_overview AS SELECT a.id, a.person_id, a.name, a.main, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, c.name AS country FROM person_addresses a LEFT JOIN countries c ON a.country_code = c.code;

-- Move data from organisations and people tables into respective address tables
INSERT INTO organisation_addresses (main, organisation_id, street1, street2, street3, town, county, postcode, country_code) SELECT 'true', id, street1, street2, street3, town, county, postcode, country_code FROM organisations;
INSERT INTO person_addresses (main, person_id, street1, street2, street3, town, county, postcode, country_code) SELECT 'true', id, street1, street2, street3, town, county, postcode, country_code FROM people;

-- Tidy up tables by dropping moved columns
ALTER TABLE organisations DROP COLUMN street1;
ALTER TABLE organisations DROP COLUMN street2;
ALTER TABLE organisations DROP COLUMN street3;
ALTER TABLE organisations DROP COLUMN town;
ALTER TABLE organisations DROP COLUMN county;
ALTER TABLE organisations DROP COLUMN postcode;
ALTER TABLE organisations DROP COLUMN country_code;
ALTER TABLE people DROP COLUMN street1;
ALTER TABLE people DROP COLUMN street2;
ALTER TABLE people DROP COLUMN street3;
ALTER TABLE people DROP COLUMN town;
ALTER TABLE people DROP COLUMN county;
ALTER TABLE people DROP COLUMN postcode;
ALTER TABLE people DROP COLUMN country_code;


--
-- recently_viewed_cleanup.sql
--

ALTER TABLE recently_viewed ADD COLUMN organisation_id INT REFERENCES organisations(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN person_id INT REFERENCES people(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN opportunity_id INT REFERENCES opportunities(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN activity_id INT REFERENCES tactile_activities(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE recently_viewed SET organisation_id = link_id WHERE type = 'organisations' AND link_id IN (SELECT id FROM organisations);
UPDATE recently_viewed SET person_id = link_id WHERE type = 'people' AND link_id IN (SELECT id FROM people);
UPDATE recently_viewed SET opportunity_id = link_id WHERE type = 'opportunities' AND link_id IN (SELECT id FROM opportunities);
UPDATE recently_viewed SET activity_id = link_id WHERE type = 'activities' AND link_id IN (SELECT id FROM tactile_activities);

DELETE FROM recently_viewed WHERE organisation_id IS NULL AND person_id IS NULL AND opportunity_id IS NULL AND activity_id IS NULL;


--
-- multiple_addresses_indexes.sql
--

create index organisation_addresses_organisation_id_main on organisation_addresses(organisation_id, main);
create index person_addresses_person_id_main on person_addresses(person_id, main);


--
-- last_contacted.sql
--

-- CREATE PROCEDURAL LANGUAGE plpgsql;

alter table organisations add column last_contacted timestamp;
alter table organisations add column last_contacted_by varchar;
alter table organisations add constraint last_contacted_by foreign key (last_contacted_by) references users(username) on update cascade on delete cascade;
alter table people add column last_contacted timestamp;
alter table people add column last_contacted_by varchar;
alter table people add constraint last_contacted_by foreign key (last_contacted_by) references users(username) on update cascade on delete cascade;
CREATE OR REPLACE FUNCTION contact_stamp() RETURNS trigger AS $contact_stamp$
    BEGIN
        IF NEW.usercompanyid IS NULL THEN
            RAISE EXCEPTION 'usercompanyid cannot be null';
        END IF;
IF NEW.owner IS NULL THEN
            RAISE EXCEPTION 'owner cannot be null';
        END IF;

-- If the organisation_id isn't null, update the org
IF NEW.organisation_id IS NOT NULL THEN
        update organisations set last_contacted=NEW.created, last_contacted_by=NEW.owner WHERE usercompanyid=NEW.usercompanyid AND id=NEW.organisation_id AND (NEW.created>=last_contacted OR last_contacted IS NULL);
END IF;
IF NEW.person_id IS NOT NULL THEN
        update people set last_contacted=NEW.created, last_contacted_by=NEW.owner WHERE usercompanyid=NEW.usercompanyid AND id=NEW.person_id AND (NEW.created>=last_contacted OR last_contacted IS NULL);
END IF;

        RETURN NULL;
    END;
$contact_stamp$ LANGUAGE plpgsql;
create trigger contact_stamp after insert  or update on emails for each row execute procedure contact_stamp();

-- Update orgs with last contacted from notes
update organisations set last_contacted=n.created, last_contacted_by=o.owner FROM notes o, (select organisation_id, max(created) as created from notes where organisation_id IS NOT NULL group by organisation_id) n WHERE n.created=o.created AND organisations.id=o.organisation_id ;

-- update orgs with last contacted from emails
update organisations set last_contacted=e.created, last_contacted_by=o.owner FROM emails o, (select organisation_id, max(created) as created from emails where organisation_id IS NOT NULL group by organisation_id) e WHERE e.created=o.created AND organisations.id=o.organisation_id AND e.created > organisations.last_contacted ;

-- Update people with last contacted from notes
update people set last_contacted=n.created, last_contacted_by=p.owner FROM notes p, (select person_id, max(created) as created from notes where person_id IS NOT NULL group by person_id) n WHERE n.created=p.created AND people.id=p.person_id ;

-- update people with last contacted from emails
update people set last_contacted=e.created, last_contacted_by=p.owner FROM emails p, (select person_id, max(created) as created from emails where person_id IS NOT NULL group by person_id) e WHERE e.created=p.created AND people.id=p.person_id AND e.created > people.last_contacted ;


--
-- account_logos.sql
--

ALTER TABLE s3_files ADD COLUMN account_id INT REFERENCES tactile_accounts(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE s3_files ADD UNIQUE (usercompanyid, account_id);


--
-- google_login.sql (not sure how this didn't get added)
--

ALTER TABLE tactile_accounts ADD COLUMN google_apps_domain varchar UNIQUE;
ALTER TABLE tactile_accounts ADD COLUMN openid varchar UNIQUE;
ALTER TABLE users ADD COLUMN google_apps_email varchar;
ALTER TABLE users ADD COLUMN openid varchar UNIQUE;


--
-- optimisation_email_timeline.sql
--

update s3_files set email_id = null where id in (select f.id from s3_files f left join emails e on e.id=f.email_id where f.email_id is not null and e.id is null);

alter table s3_files add constraint s3_files_email_id_fkey FOREIGN KEY (email_id) references emails(id) ON UPDATE CASCADE ON DELETE SET NULL;

DROP VIEW timeline_email_attachment_count CASCADE;
CREATE VIEW timeline_email_attachment_count AS
SELECT s3.email_id, s3.organisation_id, s3.person_id, count(*) AS count FROM s3_files s3 WHERE s3.email_id IS NOT NULL GROUP BY s3.email_id, s3.organisation_id, s3.person_id;

CREATE VIEW timeline_emails AS
SELECT DISTINCT e.id, 'email'::character varying(20) AS type, e.usercompanyid, e.received AS "when", e.subject AS title, e.body, e.email_from, e.email_to, ea.count AS email_attachments, e.created, e.created AS lastupdated, e.received, e.received AS due, e.received AS completed, false AS private, false AS overdue, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::character varying(20)
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::character varying(20)
            ELSE ''::character varying(20)
        END AS direction, e.owner, e.owner AS assigned_to, e.owner AS assigned_by, e.organisation_id, e.person_id, e.opportunity_id, e.activity_id, (p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
   FROM emails e
   LEFT JOIN people p ON e.person_id = p.id
   LEFT JOIN timeline_email_attachment_count ea ON e.id = ea.email_id AND ea.organisation_id = e.organisation_id AND ea.person_id = e.person_id
   LEFT JOIN organisations o ON e.organisation_id = o.id
   LEFT JOIN opportunities opp ON e.opportunity_id = opp.id
   LEFT JOIN tactile_activities act ON e.activity_id = act.id
   LEFT JOIN users u ON u.username::text = e.owner::text
   LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text)
  ORDER BY e.id, 'email'::character varying(20), e.usercompanyid, e.received, e.subject, e.body, e.email_from, e.email_to, ea.count, e.created, false::boolean, 
CASE
    WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::character varying(20)
    WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::character varying(20)
    ELSE ''::character varying(20)
END, e.owner, e.organisation_id, e.person_id, e.opportunity_id, e.activity_id, (p.firstname::text || ' '::text) || p.surname::text, o.name, opp.name, act.name, e.created, e.received, e.received, e.received, false::boolean, e.owner, e.owner;


--
-- timeline_optimisation.sql
--

DROP VIEW timeline_activities_completed;
CREATE VIEW timeline_activities_completed AS
SELECT
	ca.id, 'completed_activity'::character varying(20) AS type, ca.usercompanyid, ca.completed AS "when", ca.name AS title, ca.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	ca.created, ca.lastupdated, ca.created AS received, ca.due, ca.completed,
	false AS private, ca.overdue, NULL::character varying(20) AS direction,
	ca.owner, ca.alteredby, ca.assigned_to, ca.owner AS assigned_by,
	ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview ca
LEFT JOIN people p ON ca.person_id = p.id
LEFT JOIN organisations org ON ca.organisation_id = org.id
LEFT JOIN opportunities opp ON ca.opportunity_id = opp.id
WHERE ca.completed IS NOT NULL;


DROP VIEW timeline_activities_new;
CREATE VIEW timeline_activities_new AS
SELECT
	na.id, 'new_activity'::character varying(20) AS type, na.usercompanyid, na.created AS "when", na.name AS title, na.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	na.created, na.lastupdated, na.created AS received, na.due, na.completed,
	false AS private, na.overdue, NULL::character varying(20) AS direction,
	na.owner, na.alteredby, na.assigned_to, na.owner AS assigned_by,
	na.organisation_id, na.person_id, na.opportunity_id, na.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview na
LEFT JOIN people p ON na.person_id = p.id
LEFT JOIN organisations org ON na.organisation_id = org.id
LEFT JOIN opportunities opp ON na.opportunity_id = opp.id
WHERE na.completed IS NULL AND NOT na.overdue;


DROP VIEW timeline_activities_overdue;
CREATE VIEW timeline_activities_overdue AS
SELECT
	oa.id, 'overdue_activity'::character varying(20) AS type, oa.usercompanyid, oa.due AS "when", oa.name AS title, oa.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	oa.created, oa.lastupdated, oa.created AS received, oa.due, oa.completed,
	false AS private, oa.overdue, NULL::character varying(20) AS direction,
	oa.owner, oa.alteredby, oa.assigned_to, oa.owner AS assigned_by,
	oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview oa
LEFT JOIN people p ON oa.person_id = p.id
LEFT JOIN organisations org ON oa.organisation_id = org.id
LEFT JOIN opportunities opp ON oa.opportunity_id = opp.id
WHERE oa.completed IS NULL AND oa.overdue AND oa.class::text <> 'event'::text;


DROP VIEW timeline_emails;
CREATE VIEW timeline_emails AS
SELECT
	e.id, 'email'::character varying(20) AS type, e.usercompanyid, e.received AS "when", e.subject AS title, e.body,
	e.email_from, e.email_to, 0 AS email_attachments, 0 as size,
	e.created, e.created AS lastupdated, e.received, e.received AS due, e.received AS completed,
	false AS private, false AS overdue, 
        CASE
            WHEN pcm.contact::text = e.email_from::text THEN 'outgoing'::character varying(20)
            WHEN pcm.contact::text = e.email_to::text THEN 'incoming'::character varying(20)
            ELSE ''::character varying(20)
        END AS direction,
	e.owner, e.owner AS alteredby, e.owner AS assigned_to, e.owner AS assigned_by,
	e.organisation_id, e.person_id, e.opportunity_id, e.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM emails e
LEFT JOIN people p ON e.person_id = p.id
LEFT JOIN organisations o ON e.organisation_id = o.id
LEFT JOIN opportunities opp ON e.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON e.activity_id = act.id
LEFT JOIN users u ON u.username::text = e.owner::text
LEFT JOIN person_contact_methods pcm ON pcm.person_id = u.person_id AND (e.email_from::text = pcm.contact::text OR e.email_to::text = pcm.contact::text);


DROP VIEW timeline_flags;
CREATE VIEW timeline_flags AS
SELECT
	f.id, 'flag'::character varying(20) AS type, f.usercompanyid, f.created AS "when", f.title, f.title AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	f.created, f.created AS lastupdated, f.created AS received, f.created AS due, f.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	f.owner, f.owner AS alteredby, f.owner AS assigned_to, f.owner AS assigned_by,
	f.organisation_id, f.person_id, f.opportunity_id, f.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM flags f
LEFT JOIN people p ON f.person_id = p.id
LEFT JOIN organisations o ON f.organisation_id = o.id
LEFT JOIN opportunities opp ON f.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON f.activity_id = act.id;


DROP VIEW timeline_notes;
CREATE VIEW timeline_notes AS
SELECT
	n.id, 'note'::character varying(20) AS type, n.usercompanyid, n.lastupdated AS "when", n.title, n.note AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	n.created, n.lastupdated, n.lastupdated AS received, n.lastupdated AS due, n.lastupdated AS completed,
	n.private, false AS overdue, NULL::character varying(20) AS direction,
	n.owner, n.alteredby, n.owner AS assigned_to, n.owner AS assigned_by,
	n.organisation_id, n.person_id, n.opportunity_id, n.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM notes n
LEFT JOIN people p ON n.person_id = p.id
LEFT JOIN organisations o ON n.organisation_id = o.id
LEFT JOIN opportunities opp ON n.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON n.activity_id = act.id
WHERE NOT n.deleted;


DROP VIEW timeline_opportunities;
CREATE VIEW timeline_opportunities AS
SELECT
	o.id, 'opportunity'::character varying(20) AS type, o.usercompanyid, o.created AS "when", o.name AS title, o.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	o.created, o.created AS lastupdated, o.created AS received, o.created AS due, o.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	o.owner, o.alteredby, o.owner AS assigned_to, o.owner AS assigned_by,
	o.organisation_id, o.person_id, o.id AS opportunity_id, o.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, ''::character varying AS opportunity, ''::character varying AS activity
FROM opportunities o
LEFT JOIN people p ON o.person_id = p.id
LEFT JOIN organisations org ON o.organisation_id = org.id
WHERE o.archived = false;


DROP VIEW timeline_s3_files;
CREATE VIEW timeline_s3_files AS
SELECT
	s3.id, 's3file'::character varying(20) AS type, s3.usercompanyid, s3.created AS "when", s3.filename AS title, s3.filename AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, s3.size,
	s3.created, s3.created AS lastupdated, s3.created AS received, s3.created AS due, s3.created AS completed,
	false AS private, false AS overdue, NULL::character varying(20) AS direction,
	s3.owner, s3.owner AS alteredby, s3.owner AS assigned_to, s3.owner AS assigned_by,
	s3.organisation_id, s3.person_id, s3.opportunity_id, s3.activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, o.name AS organisation, opp.name AS opportunity, act.name AS activity
FROM s3_files s3
LEFT JOIN people p ON s3.person_id = p.id
LEFT JOIN organisations o ON s3.organisation_id = o.id
LEFT JOIN opportunities opp ON s3.opportunity_id = opp.id
LEFT JOIN tactile_activities act ON s3.activity_id = act.id
WHERE s3.email_id IS NULL AND s3.ticket_id IS NULL AND s3.bucket::text <> 'tactile_public'::text;


--
-- deleting_accounts.sql
--

ALTER TABLE user_company_access DROP CONSTRAINT user_company_access_username_fkey;
ALTER TABLE user_company_access ADD FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE notes DROP CONSTRAINT notes_alteredby_fkey;
ALTER TABLE notes ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE notes DROP CONSTRAINT notes_owner_fkey;
ALTER TABLE notes ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE people DROP CONSTRAINT person_alteredby_fkey;
ALTER TABLE people ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE people DROP CONSTRAINT person_owner_fkey;
ALTER TABLE people ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE opportunities DROP CONSTRAINT "$9";
ALTER TABLE opportunities ADD FOREIGN KEY (alteredby) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE s3_files DROP CONSTRAINT s3_files_owner_fkey;
ALTER TABLE s3_files ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE mail_queue_send DROP CONSTRAINT mail_queue_send_owner_fkey;
ALTER TABLE mail_queue_send ADD FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE tactile_accounts DROP CONSTRAINT tactile_accounts_company_id_fkey;
ALTER TABLE tactile_accounts ADD FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE tactile_accounts ADD COLUMN cancelled TIMESTAMP;
UPDATE tactile_accounts SET cancelled = account_expires WHERE NOT enabled;

UPDATE tactile_accounts SET cancelled = CASE
	WHEN account_expires > now() THEN now()
	ELSE account_expires
END
WHERE site_address IN (SELECT site_address
FROM tactile_accounts ta
WHERE NOT ta.enabled
GROUP BY ta.site_address, ta.account_expires
HAVING NOT EXISTS (SELECT username FROM users u WHERE username like '%//'||ta.site_address AND u.enabled) ORDER BY ta.site_address);


--
-- logo_checker.sql
--

alter table people add column last_logo_check timestamp;


--
-- facebook_contact.sql
--

INSERT INTO contact_method_order (type, position) VALUES ('K', '10');


--
-- querybuilder.sql
--

ALTER TABLE custom_field_map ADD COLUMN value_numeric NUMERIC;
UPDATE custom_field_map cfm SET value_numeric = CASE
    WHEN value = '' THEN NULL
    ELSE value::float
    END
    FROM custom_fields cf
    WHERE cf.id = cfm.field_id AND cf.type = 'n' AND cfm.value IS NOT NULL;
DROP VIEW custom_field_map_overview;
CREATE VIEW custom_field_map_overview AS
SELECT m.id, m.field_id, m.organisation_id, m.person_id, m.opportunity_id, m.activity_id, m.hash, m.value, m.value_numeric, m.enabled, m.option, f.name, f.type, o.value AS option_name
    FROM custom_field_map m
    LEFT JOIN custom_fields f ON m.field_id = f.id
    LEFT JOIN custom_field_options o ON m.option = o.id;
CREATE TABLE advanced_searches (
    id SERIAL PRIMARY KEY,
    owner VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
    usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    name VARCHAR NOT NULL,
    record_type VARCHAR NOT NULL,
    query TEXT NOT NULL
);
ALTER TABLE users ADD COLUMN beta BOOLEAN NOT NULL DEFAULT FALSE;


--
-- activity_tracks.sql
--

CREATE TABLE activity_tracks (
	id SERIAL PRIMARY KEY,
	name VARCHAR NOT NULL,
	description TEXT,
	usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	created TIMESTAMP NOT NULL DEFAULT now(),
	owner VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
	lastupdated TIMESTAMP NOT NULL DEFAULT now(),
	alteredby VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE activity_track_stages (
	id SERIAL PRIMARY KEY,
	track_id INT NOT NULL REFERENCES activity_tracks(id) ON UPDATE CASCADE ON DELETE CASCADE,
	name VARCHAR NOT NULL,
	description TEXT,
	type_id INT REFERENCES activitytype(id) ON UPDATE CASCADE ON DELETE CASCADE,
	x_days INT,
	assigned_to VARCHAR REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE
);


--
-- user_logins.sql
--

CREATE TABLE user_logins (
	login_time TIMESTAMP NOT NULL DEFAULT now(),
	username varchar NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
	was_successful BOOLEAN NOT NULL DEFAULT TRUE
);


--
-- timezone_overdue_fix.sql
--

DROP VIEW tactile_activities_overview CASCADE;

CREATE VIEW tactile_activities_overview AS
SELECT a.id, a.name, a.description, a.location, a.class, a.type_id, a.opportunity_id, a.organisation_id, a.person_id, a.date, a."time", a.later, a.end_date, a.end_time, a.completed, a.assigned_to, a.assigned_by, a.owner, a.alteredby, a.created, a.lastupdated, a.usercompanyid, t.name AS type, o.name AS opportunity, org.name AS organisation, (p.firstname::text || ' '::text) || p.surname::text AS person, 
        CASE
            WHEN a.later = true THEN false
            WHEN a."time" IS NULL THEN a.date < now()::date
            ELSE (a.date + a."time") < now()
        END AS overdue, 
        CASE
            WHEN a.later = true THEN 'infinity'::timestamp without time zone
            WHEN a."time" IS NULL THEN a.date + '23:59:59'::time without time zone
            ELSE a.date + a."time"
        END AS due
   FROM tactile_activities a
   LEFT JOIN activitytype t ON t.id = a.type_id
   LEFT JOIN opportunities o ON o.id = a.opportunity_id
   LEFT JOIN organisations org ON org.id = a.organisation_id
   LEFT JOIN people p ON p.id = a.person_id
   LEFT JOIN users u ON u.username::text = a.assigned_to::text;

CREATE VIEW timeline_activities_completed AS
SELECT
	ca.id, 'completed_activity'::character varying(20) AS type, ca.usercompanyid, ca.completed AS "when", ca.name AS title, ca.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	ca.created, ca.lastupdated, ca.created AS received, ca.due, ca.completed,
	false AS private, ca.overdue, NULL::character varying(20) AS direction,
	ca.owner, ca.alteredby, ca.assigned_to, ca.owner AS assigned_by,
	ca.organisation_id, ca.person_id, ca.opportunity_id, ca.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview ca
LEFT JOIN people p ON ca.person_id = p.id
LEFT JOIN organisations org ON ca.organisation_id = org.id
LEFT JOIN opportunities opp ON ca.opportunity_id = opp.id
WHERE ca.completed IS NOT NULL;


CREATE VIEW timeline_activities_new AS
SELECT
	na.id, 'new_activity'::character varying(20) AS type, na.usercompanyid, na.created AS "when", na.name AS title, na.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	na.created, na.lastupdated, na.created AS received, na.due, na.completed,
	false AS private, na.overdue, NULL::character varying(20) AS direction,
	na.owner, na.alteredby, na.assigned_to, na.owner AS assigned_by,
	na.organisation_id, na.person_id, na.opportunity_id, na.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview na
LEFT JOIN people p ON na.person_id = p.id
LEFT JOIN organisations org ON na.organisation_id = org.id
LEFT JOIN opportunities opp ON na.opportunity_id = opp.id
WHERE na.completed IS NULL AND NOT na.overdue;


CREATE VIEW timeline_activities_overdue AS
SELECT
	oa.id, 'overdue_activity'::character varying(20) AS type, oa.usercompanyid, oa.due AS "when", oa.name AS title, oa.description AS body,
	''::character varying AS email_from, ''::character varying AS email_to, 0 AS email_attachments, 0 as size,
	oa.created, oa.lastupdated, oa.created AS received, oa.due, oa.completed,
	false AS private, oa.overdue, NULL::character varying(20) AS direction,
	oa.owner, oa.alteredby, oa.assigned_to, oa.owner AS assigned_by,
	oa.organisation_id, oa.person_id, oa.opportunity_id, oa.id AS activity_id,
	(p.firstname::text || ' '::text) || p.surname::text AS person, org.name AS organisation, opp.name AS opportunity, ''::character varying AS activity
FROM tactile_activities_overview oa
LEFT JOIN people p ON oa.person_id = p.id
LEFT JOIN organisations org ON oa.organisation_id = org.id
LEFT JOIN opportunities opp ON oa.opportunity_id = opp.id
WHERE oa.completed IS NULL AND oa.overdue AND oa.class::text <> 'event'::text;


--
-- quantum.sql
--

alter table organisations add column quantum_key uuid;
alter table people add column quantum_key uuid;
alter table opportunities add column quantum_key uuid;
alter table tactile_activites add column quantum_key uuid;
alter table notes add column quantum_key uuid;


--
-- quantum_migrations.sql
--

alter table organisation_addresses add column quantum_key uuid;
alter table person_addresses add column quantum_key uuid;
alter table organisation_contact_methods add column quantum_key uuid;
alter table person_contact_methods add column quantum_key uuid;
alter table tactile_accounts add column quantum_schema uuid;
alter table users add column quantum_login varchar unique;


--
-- quantum_migration_tickets.sql
--

ALTER TABLE tickets ADD COLUMN quantum_key uuid;
ALTER TABLE ticket_comments ADD COLUMN quantum_key uuid;

