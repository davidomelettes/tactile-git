BEGIN;

ALTER TABLE employees ADD CONSTRAINT employee_unique_per_usercompany UNIQUE(employee_number, usercompanyid);

COMMIT;