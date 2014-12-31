begin;

create table mf_depts
(id serial primary key,
 dept_code varchar not null,
 dept varchar not null,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (dept_code, usercompanyid)
);

create table mf_centres
(id serial primary key,
 centre_code varchar not null,
 centre varchar not null,
 mfdept_id int not null
   references mf_depts(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (centre_code, usercompanyid)
);

create view mf_centresoverview
as select c.*, d.dept AS mfdept
from mf_centres c join mf_depts d
on c.mfdept_id = d.id; 

create table mf_resources
(id serial primary key,
 resource_code varchar not null,
 description varchar not null,
 resource_rate float not null,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (resource_code, usercompanyid)
);

create table st_items
(id serial primary key,
 item_code varchar not null,
 description varchar ,
 alpha_code varchar ,
 uom varchar ,
 prod_group varchar ,
 type_code varchar ,
 comp_class varchar ,
 abc_class varchar ,
 ref1 varchar ,
 ref2 varchar ,
 ref3 varchar ,
 text1 varchar ,
 decimals int,
 balance int,
 min_qty int,
 max_qty int,
 float_qty int,
 free_qty int,
 price float,
 cost float,
 std_cost float,
 std_mat float,
 std_lab float,
 std_ohd float,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (item_code, usercompanyid)
);

create table mf_operations
(id serial primary key,
 op_no int not null,
 start_date date ,
 end_date date ,
 qty int,
 unit varchar ,
 remarks varchar ,
 stitem_id int not null
   references st_items(id)
   on update cascade,
 mfcentre_id int not null
   references mf_centres(id)
   on update cascade,
 mfresource_id int not null
   references mf_resources(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (stitem_id, op_no, usercompanyid)
);

create view mf_operationsoverview
as select o.*, s.description AS stitem
from mf_operations o join st_items s
on o.stitem_id = s.id; 

create table mf_structures
(id serial primary key,
 line_no int not null,
 start_date date ,
 end_date date ,
 qty int,
 uom varchar ,
 remarks varchar ,
 waste_pc float ,
 stitem_id int not null
   references st_items(id)
   on update cascade,
 ststructure_id int not null
   references st_items(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (stitem_id, line_no, usercompanyid)
);

create view mf_structuresoverview
as select st.*, si.description AS stitem
from mf_structures st join st_items si
on st.stitem_id = si.id; 

create table mf_workorders
(id serial primary key,
 work_order_no varchar not null,
 order_qty int,
 made_qty int,
 required_by date not null,
 job varchar not null,
 text1 varchar ,
 text2 varchar ,
 text3 varchar ,
 order_no varchar not null,
 order_line int,
 status varchar not null,
 stitem_id int not null
   references st_items(id)
   on update cascade,
 usercompanyid bigint not null
   references company(id)
   on update cascade
   on delete cascade,
 unique (work_order_no, usercompanyid)
);

create view mf_workordersoverview
as select w.*, s.description AS stitem
from mf_workorders w join st_items s
on w.stitem_id = s.id; 

commit;
