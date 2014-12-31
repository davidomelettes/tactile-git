BEGIN;
ALTER TABLE tactile_accounts add enabled boolean not null default true;

ALTER TABLE payment_records add authorised boolean not null default false;
ALTER TABLE payment_records add trans_id varchar;
ALTER TABLE payment_records ADD type varchar NOT NULL;
ALTER TABLE payment_records ADD payment_id integer references payment_records(id) on update cascade;
COMMIT;