BEGIN;
CREATE TABLE store_orders (
    id integer DEFAULT nextval(('store_order_id_seq'::text)::regclass) NOT NULL,
    customer_id integer,
    created timestamp without time zone DEFAULT now(),
    status character varying,
    billing_firstname character varying,
    billing_surname character varying,
    billing_street1 character varying NOT NULL,
    billing_street2 character varying,
    billing_street3 character varying,
    billing_town character varying NOT NULL,
    billing_county character varying NOT NULL,
    billing_postcode character varying NOT NULL,
    billing_countrycode character(2),
    billing_name character varying,
    shipping_firstname character varying,
    shipping_surname character varying,
    shipping_street1 character varying NOT NULL,
    shipping_street2 character varying,
    shipping_street3 character varying,
    shipping_town character varying NOT NULL,
    shipping_county character varying NOT NULL,
    shipping_postcode character varying NOT NULL,
    shipping_countrycode character(2),
    shipping_name character varying,
    company_id integer NOT NULL,
    email character varying,
    currency character varying
);

ALTER TABLE ONLY store_orders
    ADD CONSTRAINT store_order_pkey PRIMARY KEY (id);

ALTER TABLE ONLY store_orders
    ADD CONSTRAINT store_order_billing_countrycode_fkey FOREIGN KEY (billing_countrycode) REFERENCES countries(code);

ALTER TABLE ONLY store_orders
    ADD CONSTRAINT store_order_companyid_fkey FOREIGN KEY (company_id) REFERENCES company(id);

ALTER TABLE ONLY store_orders
    ADD CONSTRAINT store_order_customerid_fkey FOREIGN KEY (customer_id) REFERENCES customers(id);

ALTER TABLE ONLY store_orders
    ADD CONSTRAINT store_order_shipping_countrycode_fkey FOREIGN KEY (shipping_countrycode) REFERENCES countries(code);

CREATE TABLE store_order_items (
    order_id integer NOT NULL,
    product_id integer NOT NULL,
    price numeric,
    quantity integer
);

ALTER TABLE ONLY store_order_items
    ADD CONSTRAINT store_order_items_pkey PRIMARY KEY (order_id, product_id);

ALTER TABLE ONLY store_order_items
    ADD CONSTRAINT store_order_items_orderid_fkey FOREIGN KEY (order_id) REFERENCES store_orders(id) ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE ONLY store_order_items
    ADD CONSTRAINT store_order_items_productid_fkey FOREIGN KEY (product_id) REFERENCES store_products(id);
COMMIT;
