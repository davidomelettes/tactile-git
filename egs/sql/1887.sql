BEGIN;
ALTER TABLE store_config ALTER default_customer_type_id DROP NOT NULL;
ALTER TABLE store_config ADD admin_email varchar;
alter table store_config add message_signature text;
alter table store_config add store_name varchar;
COMMIT;
