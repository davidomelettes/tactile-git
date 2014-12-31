begin;
alter table product_attributes alter column units drop not null;
commit;
