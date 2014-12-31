begin;
drop view website_filesoverview;
create view website_filesoverview as
SELECT w.website_id, w.file_id, w.id, f.name AS file
   FROM website_files w, file f
  WHERE w.file_id = f.id;
commit;
