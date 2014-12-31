begin;
alter table store_vouchers alter column usercompanyid set not null;
commit;
