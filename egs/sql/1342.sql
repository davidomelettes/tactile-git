BEGIN;
CREATE TABLE store_order_extras (
id serial primary key,
name varchar not null,
price numeric not null default 0,
shipping boolean not null default true,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

CREATE TABLE store_order_selected_extras (
id serial primary key,
order_id int not null references store_orders(id) on update cascade on delete cascade,
quantity int not null default 1,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);
COMMIT;