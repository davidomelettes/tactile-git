begin;
alter table store_products add column unspsc_code varchar(10);
commit;
