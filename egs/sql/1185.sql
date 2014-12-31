begin;
alter table activities drop constraint "$5";
alter table activities add constraint "$5" foreign key (opportunity_id) references opportunities(id) on update cascade on delete set null;
commit;
