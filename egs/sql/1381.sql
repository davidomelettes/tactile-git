begin;
alter table intranet_layouts add column css text;
create view intranet_page_accessoverview as
SELECT ir.*, hr.username
   FROM intranet_page_access ir
   LEFT JOIN hasrole hr ON ir.role_id = hr.roleid;
commit;
