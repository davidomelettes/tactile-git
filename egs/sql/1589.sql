begin;
create table sales_people (
	id bigserial primary key,
	person_id bigint not null references person(id) on update cascade on delete cascade,
	base_commission_rate bigint not null
);
create view sales_people_overview as
select sp.*, p.firstname || ' ' || p.surname as person from sales_people sp join person p on sp.person_id = p.id;
commit;
