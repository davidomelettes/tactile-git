begin;
alter table store_vouchers add column expiry date not null;
commit;
