begin;
create table task_resources (
id bigserial primary key,
resource_id bigint not null references resources(id) on update cascade on delete cascade,
task_id bigint not null references tasks(id) on update cascade on delete cascade);
create view task_resources_overview as select tr.*, p.firstname || ' ' || p.surname as resource from task_resources tr join resources r on r.id=tr.resource_id join person p on p.id=r.person_id;
alter table task_resources add constraint resource_task_unique_key unique(resource_id,task_id);
commit;
