begin;
create view intranet_page_filesoverview as 
select ip.*, f.name as file from intranet_page_files ip, file f where ip.file_id = f.id;
create view intranet_section_filesoverview as
select isf.*, f.name as file from intranet_section_files isf, file f where isf.file_id = f.id;
commit;
