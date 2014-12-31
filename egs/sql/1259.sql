BEGIN;
ALTER TABLE newsletter_recipients ADD newsletter_id int references newsletters(id) on update cascade on delete cascade;
COMMIT;
