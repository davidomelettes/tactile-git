BEGIN;
UPDATE recently_viewed SET type='organisations' where type='leads' or type='clients';
commit;
