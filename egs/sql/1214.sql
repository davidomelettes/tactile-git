begin;

create table wh_stores
(id serial primary key,
 store_code varchar not null,
 description varchar ,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (store_code, usercompanyid)
);

create table wh_locations
(id serial primary key,
 location varchar not null,
 description varchar ,
 whstore_id int not null
   references wh_stores(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (location, usercompanyid)
);

create view wh_locationsoverview
as select l.*, s.description AS whstore
from wh_locations l join wh_stores s
on l.whstore_id = s.id; 

create table wh_bins
(id serial primary key,
 bin_code varchar not null,
 description varchar ,
 whlocation_id int not null
   references wh_locations(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (bin_code, usercompanyid)
);

create view wh_binsoverview
as select b.*, l.description AS whlocation
from wh_bins b join wh_locations l
on b.whlocation_id = l.id; 

create table st_balances
(id serial primary key,
 balance int,
 whstore_id int not null
   references wh_stores(id)
   on update cascade,
 whlocation_id int not null
   references wh_locations(id)
   on update cascade,
 whbin_id int not null
   references wh_bins(id)
   on update cascade,
 stitem_id int not null
   references st_items(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade
);

create view st_balancesoverview
as select bl.*,
 st.description AS whstore,
 l.description AS whlocation,
 b.description AS whbin,
 si.description AS stitem
from st_balances bl
 join wh_stores st
on bl.whstore_id = st.id
 join wh_locations l
on bl.whlocation_id = l.id
 join wh_bins b
on bl.whbin_id = b.id
 join st_items si
on bl.stitem_id = si.id; 

create table st_transactions
(id serial primary key,
 balance int,
 created timestamp not null default now() ,
 cost numeric,
 std_cost numeric,
 std_mat numeric,
 std_lab numeric,
 std_ohd numeric,
 whstore_id int not null
   references wh_stores(id)
   on update cascade,
 whlocation_id int not null
   references wh_locations(id)
   on update cascade,
 whbin_id int not null
   references wh_bins(id)
   on update cascade,
 stitem_id int not null
   references st_items(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade
);

create view st_transactionsoverview
as select t.*,
 st.description AS whstore,
 l.description AS whlocation,
 b.description AS whbin,
 si.description AS stitem
from st_transactions t
 join wh_stores st
on t.whstore_id = st.id
 join wh_locations l
on t.whlocation_id = l.id
 left join wh_bins b
on t.whbin_id = b.id
 join st_items si
on t.stitem_id = si.id; 
 

alter table st_items
alter price type numeric,
alter cost type numeric,
alter std_cost type numeric,
alter std_mat type numeric,
alter std_lab type numeric,
alter std_ohd type numeric
;

commit;
