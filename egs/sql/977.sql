BEGIN;
CREATE TABLE customer_types (
id serial primary key,
name varchar not null,
title varchar not null,
customer_can_select boolean not null default false,
customers_can_login boolean not null default true,
requires_confirmation boolean not null default false,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);



CREATE TABLE customers_in_types (
customer_id int not null references customers(id) on update cascade on delete cascade,
customertype_id int not null references customer_types(id) On update cascade
);


CREATE TABLE customer_type_discounts (
id serial primary key,
title varchar not null,
customertype_id int not null references customer_types(id) ON UPDATE CASCADE ON DELETE CASCADE,
valid_from date not null default now(),
valid_to date,
new_customers_only boolean not null default false,
lasts_for interval,
discount_type varchar not null,
discount_amount numeric not null default 0,
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE TABLE store_config (
id serial primary key,
site_status varchar not null,
default_products_per_page int not null default 10,
default_customer_type_id int not null references customer_types(id) on update cascade,
meta_description text,
meta_keywords text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade unique
);

COMMIT;
