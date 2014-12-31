BEGIN;
ALTER TABLE project_phases ADD position int not null default 0;
ALTER TABLE project_phases DROP project_id;
COMMIT;
