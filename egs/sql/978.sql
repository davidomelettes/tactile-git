BEGIN;

CREATE TABLE product_bundles (
id serial primary key,
title varchar not null,
description text,
valid_from date not null default now(),
valid_to date,
discount_type varchar not null,
discount_amount numeric not null default 0,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE products_in_bundles (
id serial primary key,
product_id int not null references store_products on update cascade on delete cascade,
bundle_id int  not null references product_bundles on update cascade on delete cascade,
quantity_required int not null default 1,
suggest_for boolean not null default true,
discount_type varchar not null,
discount_amount numeric not null default 0
);

COMMIT;