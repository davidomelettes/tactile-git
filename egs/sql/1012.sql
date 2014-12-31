BEGIN;
ALTER TABLE store_products ADD COLUMN feed_id BIGINT;
ALTER TABLE store_products ADD UNIQUE(feed_id);
CREATE TABLE product_attributes(
id bigserial primary key,
product_id bigint references store_products(id) on update cascade on delete cascade,
product_feed_id bigint references store_products(feed_id) on update cascade on delete cascade,
name varchar not null,
value text not null,
units varchar not null);
CREATE TABLE product_features(
id bigserial primary key,
product_id bigint references store_products(id) on update cascade on delete cascade,
product_feed_id bigint references store_products(feed_id) on update cascade on delete cascade,
description varchar not null default '',
sequence bigint not null default 0
);
CREATE TABLE product_related_products(
from_id bigint not null references store_products(id) on update cascade on delete cascade,
to_id bigint not null references store_products(id) on update cascade on delete cascade);
COMMIT;
