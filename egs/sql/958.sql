begin;
alter table store_vouchers alter column buyer_id drop not null;
commit;
