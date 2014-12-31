begin;
create table intranet_page_files (
intranetpage_id bigint not null references intranet_pages(id) on update cascade on delete cascade,
file_id bigint not null references file(id) on update cascade on delete cascade,
id bigserial primary key);
commit;
