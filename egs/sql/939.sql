begin;
create table store_vouchers (
id bigserial primary key,
code varchar not null,
buyer_id bigint not null references customers(id) on update cascade on delete no action,
redeemed_by bigint references customers(id) on update cascade on delete no action,
redeemed boolean,
usercompanyid bigint references company(id) on update cascade on delete cascade
);
commit;
