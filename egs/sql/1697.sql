begin;
insert into permissions(permission,type,description,title,display,position) 
values('hr','m','Access to all human resources actions and controllers','HR','t',9);

insert into permissions(permission,type,title,display) 
values('hr-admin','c','HR Admin','f');


create table employees(
id serial primary key, person_id int not null references person(id) on update cascade,employee_number varchar not null,next_of_kin varchar,nok_address varchar,nok_phone varchar,nok_relationship varchar,bank_name varchar,bank_address varchar,bank_account_name varchar,bank_account_number varchar,bank_sort_code varchar,start_date date not null,finished_date date, pay_frequency varchar, created timestamp not null default now(),lastupdated timestamp not null default now(),alteredby varchar references users(username) on update cascade,usercompanyid bigint not null references company(id) on update cascade on delete cascade);


insert into permissions(permission,type,title,display,position) 
values('hr-employees','c','Employees','t',10);

insert into permissions(permission,type,display) 
values('hr-employees-index','a','t');

insert into permissions(permission,type,display) 
values('hr-employees-view','a','t');

insert into permissions(permission,type,display) 
values('hr-employees-edit','a','t');

insert into permissions(permission,type,display) 
values('hr-employees-new','a','t');


create table holiday_entitlements(
id serial primary key, employee_id int not null references employees(id) on update cascade,num_days int not null,start_date date not null, end_date date not null,lastupdated date not null default now(),statutory_days boolean default false,usercompanyid bigint not null references company(id) on update cascade on delete cascade);

create table holiday_extra_days(
id serial primary key, entitlement_period_id int not null references holiday_entitlements(id) on update cascade, employee_id int not null references employees(id) on update cascade,num_days int not null,reason varchar, authorisedby varchar not null references users(username) on update cascade, authorised_on date not null default now(), usercompanyid bigint not null references company(id) on update cascade on delete cascade);

create table holiday_requests(
id serial primary key, employee_id int not null references employees(id) on update cascade,start_date date not null, end_date date not null,num_days int not null,employee_notes varchar,special_circumnstances boolean default false,approved boolean default false,approved_by varchar references users(username) on update cascade,reason_declined varchar,created date not null default now(),usercompanyid bigint not null references company(id) on update cascade on delete cascade);

insert into permissions(permission,type,title,display,position) 
values('hr-holidays','c','Holidays','f',11);

insert into permissions(permission,type,title,display,position) 
values('hr-holidayentitlements','c','Holiday Entitlements','t',12);

insert into permissions(permission,type,title,display,position) 
values('hr-holidayextradays','c','Holiday Extra Days','t',13);

insert into permissions(permission,type,title,display,position) 
values('hr-holidayrequests','c','Holiday Requests','t',14);


insert into permissions(permission,type,display) 
values('hr-holidayentitlements-index','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayentitlements-new','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayentitlements-edit','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayentitlements-view','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayentitlements-viewemployee','a','t');


insert into permissions(permission,type,display) 
values('hr-holidayextradays-index','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayextradays-new','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayextradays-edit','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayextradays-view','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayextradays-viewentitlement','a','t');


insert into permissions(permission,type,display)
values('hr-holidayrequests-new','a','t');

insert into permissions(permission,type,display)
values('hr-holidayrequests-view','a','t');

insert into permissions(permission,type,display)
values('hr-holidayrequests-edit','a','t');

insert into permissions(permission,type,display) 
values('hr-holidayrequests-viewemployee','a','t');
commit;
