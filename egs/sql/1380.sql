begin;
create table intranet_section_files (
intranetsection_id bigint not null references intranet_sections(id) on update cascade on delete cascade,
file_id bigint not null references file(id) on update cascade on delete cascade,
id bigserial primary key);
commit;
