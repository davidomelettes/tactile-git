BEGIN;
ALTER TABLE companypermissions DROP constraint companypermissions_usercompanyid_fkey;
ALTER TABLE companypermissions ADD FOREIGN KEY (usercompanyid) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;
COMMIT;