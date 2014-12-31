BEGIN;
ALTER TABLE store_order_selected_extras ADD extra_id int not null references store_order_extras;
COMMIT;