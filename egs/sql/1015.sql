BEGIN;
CREATE TABLE product_option_categories (
id bigserial primary key,
name varchar not null,
product_id bigint references store_products(id) on update cascade on delete cascade);
CREATE TABLE product_options (
id bigserial primary key,
category_id bigint references product_option_categories(id) on update cascade on delete cascade,
description varchar not null,
price bigint not null,
selected boolean not null default false);
COMMIT;
