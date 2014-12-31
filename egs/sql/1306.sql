BEGIN;
CREATE TABLE store_basket_item_options (
	id serial primary key,
	item_id int not null references store_basket_items(id) on update cascade on delete cascade,
	option_id int not null references product_options(id) on update cascade on delete cascade
);
CREATE TABLE store_order_item_options (
	id serial primary key,
	item_id int not null references store_basket_items(id) on update cascade on delete cascade,
	option_id int not null references product_options(id) on update cascade on delete cascade
);
COMMIT;
