begin;
alter table companyaddress alter county drop not null;
alter table personaddress alter county drop not null;
commit;
