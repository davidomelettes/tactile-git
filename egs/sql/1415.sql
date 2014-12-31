BEGIN;
CREATE TABLE si_header (
id serial primary key,
invoice_number int not null,
sales_order_id int,
despatch_note_id int,
slmaster_id int not null references slmaster(id) on update cascade on delete cascade,
invoice_date date not null default now(),
transaction_type varchar not null default 'I',
ext_reference varchar,
currency_id int not null references cumaster(id) on update cascade on delete cascade,
rate numeric not null,
gross_value numeric not null,
tax_value numeric not null,
net_value numeric not null,
twin_currency int not null references cumaster(id),
twin_rate numeric not null,
twin_gross_value numeric not null,
twin_tax_value numeric not null,
twin_net_value numeric not null,
base_gross_value numeric not null,
base_tax_value numeric not null,
base_net_value numeric not null,
payment_term_id int not null references syterms(id),
due_date date not null,
status varchar not null,
description text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade,
UNIQUE(invoice_number,usercompanyid)
);


CREATE TABLE si_lines(
id serial primary key,
sinvoice_id int not null references si_header(id),
line_number int not null,
sales_order_id int,
order_line_id int,
item_id int,
item_description varchar,
sales_qty numeric,
sales_price numeric,
currency_id int not null references cumaster(id) on update cascade on delete cascade,
rate numeric not null,
gross_value numeric not null,
tax_value numeric not null,
net_value numeric not null,
twin_currency int not null references cumaster(id),
twin_rate numeric not null,
twin_gross_value numeric not null,
twin_tax_value numeric not null,
twin_net_value numeric not null,
base_gross_value numeric not null,
base_tax_value numeric not null,
base_net_value numeric not null,
glaccount int not null references glmaster(id),
glcentre_id int not null references glcentre(id),
description text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

ALTER TABLE gltransaction alter linkref drop not null;
alter table gltransaction alter linkref type varchar;

drop table taxrate;
alter table taxrates alter lastupdated set default now();
alter table tax_statuses add apply_tax boolean not null default false;
alter table si_lines add line_discount numeric not null default 0;

CREATE TABLE syterms (
id serial primary key,
description varchar not null,
basis varchar not null default 'I',
days int not null,
months int not null,
discount numeric not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE TABLE tax_statuses (
id serial primary key,
description varchar not null,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

INSERT INTO tax_statuses (description, usercompanyid) VALUES ('UK',1);
INSERT INTO tax_statuses (description, usercompanyid) VALUES ('EU',1);
INSERT INTO tax_statuses (description, usercompanyid) VALUES ('Export',1);


CREATE TABLE slmaster (
id serial primary key,
name varchar not null,
company_id bigint not null references company(id) on update cascade,
currency_id int not null references cumaster(id),
statement boolean not null default true,
term_id int not null references syterms(id),
last_paid date,
tax_status_id int not null references tax_statuses(id),
created timestamp not null default now(),
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);


CREATE TABLE sltransactions(
id serial primary key,
transaction_date timestamp not null default now(),
transaction_type varchar not null,
status varchar not null,
our_reference varchar not null,
ext_reference varchar not null,
currency_id int not null references cumaster(id),
rate numeric not null,
gross_value numeric not null,
tax_value numeric not null,
net_value numeric not null,
twin_currency int not null references cumaster(id),
twin_rate numeric not null,
twin_gross_value numeric not null,
twin_tax_value numeric not null,
twin_net_value numeric not null,
base_gross_value numeric not null,
base_tax_value numeric not null,
base_net_value numeric not null,
payment_term_id int not null references syterms(id),
due_date date not null,
cross_ref varchar,
os_value numeric not null,
twin_os_value numeric not null,
base_os_value numeric not null,
description text,
usercompanyid bigint not null references company(id) on update cascade on delete cascade
);

COMMIT;
