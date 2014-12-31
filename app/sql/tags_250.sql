BEGIN;
ALTER TABLE tag_map DROP CONSTRAINT tag_map_opportunity_id_fkey;
ALTER TABLE tag_map DROP CONSTRAINT tag_map_person_id_fkey;
ALTER TABLE tag_map DROP CONSTRAINT tag_map_activity_id_fkey;
ALTER TABLE tag_map ADD FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE tag_map ADD FOREIGN KEY (person_id) REFERENCES person(id) ON UPDATE CASCADE ON DELETE CASCADE;
ALTER TABLE tag_map ADD FOREIGN KEY (activity_id) REFERENCES tactile_activities(id) ON UPDATE CASCADE ON DELETE CASCADE;
COMMIT;