begin;
alter table tag_map add id serial not null primary key;
alter table tag_map add column hash varchar;

update tag_map set hash = 'c' || company_id where company_id is not null;
update tag_map set hash = 'a' || activity_id where activity_id is not null;
update tag_map set hash = 'o' || opportunity_id where opportunity_id is not null;
update tag_map set hash = 'p' || person_id where person_id is not null;

alter table tag_map alter hash set not null;
commit;
