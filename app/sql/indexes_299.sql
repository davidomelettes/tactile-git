create index tags_name on tags(name);
create index person_surname on person(surname);
create index person_contact_methods_type_main on person_contact_methods(type,main);
create index companyroles_read on companyroles(read) where read=true;
create index tag_map_person ON tag_map(tag_id,person_id);
create index tag_map_company ON tag_map(tag_id,company_id);
create index tag_map_opportunity ON tag_map(tag_id,opportunity_id);
create index tag_map_activity ON tag_map(tag_id,activity_id);
