--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET check_function_bodies = false;
SET client_min_messages = warning;

SET search_path = public, pg_catalog;

--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: gj
--

SELECT pg_catalog.setval(pg_catalog.pg_get_serial_sequence('permissions', 'id'), 101, true);


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: gj
--

COPY permissions (id, permission, "type", description, title, display, "position") FROM stdin;
77	contacts-leads	c	\N	Leads	t	2
12	erp-index	a	\N	\N	t	\N
13	erp-setup-currencys	c	\N	\N	t	\N
14	erp-setup-currencys-save	a	\N	\N	t	\N
15	erp-setup-currencys-edit	a	\N	\N	t	\N
16	erp-setup-currencys-new	a	\N	\N	t	\N
17	erp-setup-currencys-index	a	\N	\N	t	\N
18	erp-setup-index	a	\N	\N	t	\N
20	contacts	m	\N	Contacts	t	2
23	projects	m	\N	Projects	t	4
1	erp	m	\N	Accounts/ERP	t	5
26	erp-setup	m	\N	ERP Setup	t	\N
85	contacts-companyattachments	c	\N	\N	f	\N
29	ecommerce-sections	c	\N	Sections	t	\N
24	contacts-companys	c	\N	Accounts	t	1
30	projects-projects	c	\N	Projects	t	\N
32	egs	m	EGS Super Permission	EGS	f	\N
21	admin	m	Access to admin area of EGS	Admin	f	\N
22	crm	m	Access to all CRM	CRM	t	3
10	dashboard	m	Access to all Dashbord actions	Home	t	1
11	dashboard-index	a	Access to view dashboard overview page	Dashboard Overview	t	\N
19	ecommerce	m	Access to all ecommerce actions and controllers	eCommerce	t	6
28	ecommerce-products	c	Access to all products actions	Products	t	\N
78	crm-campaigns	c	\N	Campaigns	t	3
33	admin-users	c	User administration 	Users	t	\N
34	admin-roles	c	Roles administration	Roles	t	\N
35	admin-roles-index	a			t	\N
36	admin-roles-edit	a			t	\N
37	admin-roles-save	a			t	\N
38	admin-roles-saveroles	a			t	\N
39	admin-roles-delete	a			t	\N
40	admin-users-new	a			t	\N
41	admin-users-delete	a			t	\N
43	admin-users-saveroles	a			t	\N
42	admin-users-edit	a			t	\N
44	ticketing	m	Ticketing module	Ticketing	t	7
47	websites-index	a	\N	\N	t	\N
46	websites	m	\N	Websites	t	8
25	contacts-persons	c	Access to all person actions	People	t	3
86	contacts-companynotes	c	\N	\N	f	\N
52	ecommerce-suppliers	c	\N	Suppliers	t	3
65	system_admin	m	Access to all system administration functions	System Admin	f	\N
27	crm-opportunitys	c	\N	Opportunities	t	1
49	crm-activitys	c	\N	Activities	t	2
87	contacts-companyaddresss	c	\N	\N	f	\N
88	contacts-companycontactmethods	c	\N	\N	f	\N
83	contacts-edit	a	\N	\N	t	\N
45	ticketing-index	a	\N	\N	t	\N
84	contacts-index	a	\N	\N	t	\N
89	contacts-personcontactmethods	c	\N	\N	f	\N
90	contacts-personaddresss	c	\N	\N	f	\N
60	ecommerce-orders	c	\N	Orders	t	\N
61	ecommerce-vouchers	c	\N	Vouchers	t	\N
62	projects-tasks-viewproject	a	\N	\N	t	\N
64	projects-tasks-edit	a	\N	\N	t	\N
66	projects-tasks-save	a	\N	\N	t	\N
48	ticketing-tickets	c	\N	Tickets	t	\N
54	ticketing-tickets-edit	a	\N	\N	t	\N
55	ticketing-tickets-save	a	\N	\N	t	\N
56	ticketing-tickets-new	a	\N	\N	t	\N
57	ticketing-queues-edit	a	\N	\N	t	\N
58	ticketing-queues-save	a	\N	\N	t	\N
59	ticketing-queues-new	a	\N	\N	t	\N
53	ticketing-tickets-index	a	\N	\N	t	\N
51	ticketing-queues-index	a	\N	\N	t	\N
50	ticketing-queues	c	\N	Queues	t	\N
67	dashboard-details	c	\N	My Details	t	1
63	projects-tasks-new	a	\N	\N	t	\N
68	projects-projects-new	a	\N	New Project	f	\N
69	projects-projects-view	a	\N	\N	f	\N
70	projects-projects-index	a	\N	\N	f	\N
72	crm-setup	c	\N	\N	f	\N
73	projects-setup	c	\N	\N	f	\N
74	ticketing-setup	c	\N	\N	f	\N
75	contacts-setup	c	\N	\N	f	\N
91	ticketing-hours	c	\N	\N	f	\N
92	ticketing-hours-viewticket	a	\N	\N	f	\N
93	ticketing-hours-new	a	\N	\N	f	\N
96	intranet	m	\N	Intranet	t	5
100	intranet-setup	\N	\N	Setup	t	1
101	intranet-layouts	c	\N	Layouts	f	2
\.


--
-- PostgreSQL database dump complete
--

