BEGIN;
ALTER TABLE store_order_item_options DROP item_id;
ALTER TABLE store_order_item_options ADD item_id int not null references store_order_items(id) on update cascade on delete cascade;
COMMIT;
