begin;
alter table intranet_sections drop column parent_id cascade;
alter table intranet_pages add column alteredby varchar not null references users(username) on update cascade on 
delete cascade;
alter table intranet_pages add column lastupdated timestamp not null default now();
alter table intranet_pages add column publish_on timestamp not null default now();
alter table intranet_pages add column withdraw_on timestamp;
alter table intranet_pages add column visible boolean not null default true;
alter table intranet_pages add column menuorder integer not null default 1;
alter table intranet_pages drop column "position";
commit;
