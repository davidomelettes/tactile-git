begin;
alter table calendar_events add column company_id bigint references company(id) on update cascade on delete cascade;
alter table calendar_events add column person_id bigint references person(id) on update cascade on delete cascade;
create table calendar_event_attendees (
id bigserial primary key,
calendar_event_id bigint references calendar_events(id) on update cascade on delete cascade,
person_id bigint not null references person(id) on update cascade on delete cascade,
reminder boolean not null default false,
reminder_interval interval default '15 minutes');
commit;
