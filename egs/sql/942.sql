begin;
alter table store_vouchers add column value bigint not null;
commit;
