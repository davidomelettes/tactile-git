BEGIN;
alter table lang alter code type char(10);
INSERT INTO permissions (permission,title,type,display,position) VALUES ('dashboard-details','My Details','c',true,1);
COMMIT;