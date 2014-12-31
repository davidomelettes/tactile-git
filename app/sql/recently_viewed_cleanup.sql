BEGIN;

ALTER TABLE recently_viewed ADD COLUMN organisation_id INT REFERENCES organisations(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN person_id INT REFERENCES people(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN opportunity_id INT REFERENCES opportunities(id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE recently_viewed ADD COLUMN activity_id INT REFERENCES tactile_activities(id) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE recently_viewed SET organisation_id = link_id WHERE type = 'organisations' AND link_id IN (SELECT id FROM organisations);
UPDATE recently_viewed SET person_id = link_id WHERE type = 'people' AND link_id IN (SELECT id FROM people);
UPDATE recently_viewed SET opportunity_id = link_id WHERE type = 'opportunities' AND link_id IN (SELECT id FROM opportunities);
UPDATE recently_viewed SET activity_id = link_id WHERE type = 'activities' AND link_id IN (SELECT id FROM tactile_activities);

DELETE FROM recently_viewed WHERE organisation_id IS NULL AND person_id IS NULL AND opportunity_id IS NULL AND activity_id IS NULL;

COMMIT;



