BEGIN;

CREATE TABLE store_offer_codes (
id serial primary key,
code varchar not null unique,
valid_from date not null default now(),
valid_to date,
max_uses int not null default 1,
customer_id int references customers(id) on update cascade on delete cascade,
customer_type_id int references customer_types(id) on update cascade on delete cascade,
product_id int references store_products(id) on update cascade on delete cascade,
section_id int references store_sections(id) on update cascade on delete cascade,
campaign_id int references campaigns(id) on update cascade on delete cascade,
discount_type varchar not null,
discount_amount numeric not null default 0,
notes text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE store_offer_code_uses (
offer_code_id int not null references store_offer_codes(id) on update cascade,
created timestamp not null default now(),
customer_id int not null references customers(id) on update cascade on delete cascade,
order_id int not null references store_orders(id) on update cascade on delete cascade
);


CREATE TABLE store_product_information_requests (
id serial primary key,
product_id int not null references store_products(id) on update cascade on delete cascade,
name varchar not null,
compulsory boolean not null default false,
enabled boolean not null default true,
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE order_item_additional_info (
id serial primary key,
order_id int not null references store_orders(id),
inforequest_id int references store_product_information_requests(id) on update cascade,
value text not null
);

CREATE TABLE shipping_options (
id serial primary key,
name varchar not null,
description text,
max_weight numeric not null default 0,
min_order_cost numeric not null default 0,
max_order_cost numeric not null default 0,
base_cost numeric not null,
weight_multiplier numeric not null default 0
);

ALTER TABLE store_orders add shippingoption_id int references shipping_options(id) on update cascade;

CREATE TABLE store_section_discounts (
id serial primary key,
section_id int not null references store_sections(id) on update cascade on delete cascade,
discount_type varchar not null,
discount_amount numeric not null default 0,
valid_from date not null default now(),
valid_to date,
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE TABLE store_dynamic_sections (
id serial primary key,
name varchar not null,
title varchar not null,
visible boolean not null default true,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE TABLE dynamic_section_criteria (
id serial primary key,
dynamicsection_id int not null references store_dynamic_sections(id) on update cascade on delete cascade,
property varchar not null,
operator varchar not null,
value varchar not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


drop view orderoverview;
CREATE VIEW orderoverview AS

SELECT o.*, p.firstname || ' ' || p.surname AS customer FROM store_orders o JOIN customers c 
ON (o.customer_id=c.id) JOIN person p ON (c.person_id=p.id);

drop view orderitemoverview;
CREATE VIEW orderitemoverview AS
SELECT 'Order ' || i.order_id AS order,
CASE WHEN p.productcode IS NULL then p.name ELSE p.name || ' (' || p.productcode || ')' END as product,
i.*,
CASE WHEN p.stockcontrolenable THEN p.stocklevel::varchar ELSE 'n/a' END AS stocklevel,
CASE WHEN (p.stockcontrolenable AND i.quantity>p.stocklevel) THEN false ELSE true END AS in_stock,
p.usercompanyid,
per.firstname || ' ' || per.surname AS customer
FROM store_order_items i
JOIN store_products p ON (i.product_id=p.id)
JOIN store_orders o ON (o.id=i.order_id)
JOIN customers c ON (o.customer_id=c.id)
JOIN person per ON (c.person_id=per.id);

COMMIT;
